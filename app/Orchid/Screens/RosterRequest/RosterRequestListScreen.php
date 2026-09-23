<?php

namespace App\Orchid\Screens\RosterRequest;

use App\Models\ApplicationRoster;
use App\Models\RosterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Symfony\Component\HttpFoundation\Response;

class RosterRequestListScreen extends Screen
{
    public function query(): iterable
    {
        $user = auth()->user();
        $seeAll = $user->hasAccess('platform.applications.approve')
            || $user->hasAccess('platform.applications.edit');

        $requests = RosterRequest::query()
            ->with([
                'player',
                'tournament',
                'toApplication.team',
                'fromApplication.team',
                'createdBy',
                'reviewer',
            ])
            ->when(!$seeAll, function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhereHas('toApplication.team', fn ($t) => $t->where('captain_id', $user->id))
                    ->orWhereHas('fromApplication.team', fn ($t) => $t->where('captain_id', $user->id));
            })
            ->orderByDesc('created_at')
            ->get();

        return [
            'requests' => $requests,
        ];
    }

    public function name(): ?string
    {
        return 'Дозаявки и переходы';
    }

    public function description(): ?string
    {
        return 'Заявки на дозаявку, переход и отзаявку игроков. Утверждение доступно администратору.';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Подать запрос')
                ->icon('bs.plus-lg')
                ->route('platform.roster.requests.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::modal('rejectModal', [
                Layout::rows([
                    Input::make('roster_request_id')->type('hidden'),

                    TextArea::make('rejection_reason')
                        ->title('Причина отклонения')
                        ->rows(3)
                        ->max(500),
                ]),
            ])
                ->title('Отклонить запрос')
                ->applyButton('Отклонить')
                ->closeButton('Отмена'),

            Layout::table('requests', [
                TD::make('created_at', 'Дата')
                    ->width('120px')
                    ->render(fn (RosterRequest $r) => $r->created_at?->format('d.m.Y H:i')),

                TD::make('type', 'Тип')
                    ->width('110px')
                    ->render(function (RosterRequest $r) {
                        $classes = [
                            RosterRequest::TYPE_ADDITION => 'success',
                            RosterRequest::TYPE_TRANSFER => 'info',
                            RosterRequest::TYPE_REMOVAL => 'danger',
                        ];

                        return '<span class="badge bg-' . ($classes[$r->type] ?? 'secondary') . '">'
                            . (RosterRequest::TYPES[$r->type] ?? $r->type) . '</span>';
                    }),

                TD::make('player', 'Игрок')
                    ->render(fn (RosterRequest $r) => e($r->player->name ?? '—')),

                TD::make('tournament', 'Турнир')
                    ->render(fn (RosterRequest $r) => e($r->tournament->name ?? '—')),

                TD::make('path', 'Команды')
                    ->render(function (RosterRequest $r) {
                        $to = $r->toApplication?->team?->name;
                        $from = $r->fromApplication?->team?->name;

                        return match ($r->type) {
                            RosterRequest::TYPE_ADDITION => '→ ' . e($to ?? '—'),
                            RosterRequest::TYPE_REMOVAL => e($from ?? '—') . ' →',
                            default => e($from ?? '—') . ' → ' . e($to ?? '—'),
                        };
                    }),

                TD::make('details', '№ / амплуа')
                    ->width('140px')
                    ->render(function (RosterRequest $r) {
                        if ($r->type === RosterRequest::TYPE_REMOVAL) {
                            return '<span class="text-muted">—</span>';
                        }

                        $position = \App\Models\ApplicationRoster::POSITIONS[$r->position] ?? $r->position;

                        return e((string) $r->jersey_number) . ' · ' . e((string) $position);
                    }),

                TD::make('status', 'Статус')
                    ->width('150px')
                    ->render(function (RosterRequest $r) {
                        $classes = [
                            RosterRequest::STATUS_PENDING => 'warning text-dark',
                            RosterRequest::STATUS_APPROVED => 'success',
                            RosterRequest::STATUS_REJECTED => 'danger',
                        ];
                        $class = $classes[$r->status] ?? 'secondary';
                        $label = RosterRequest::STATUSES[$r->status] ?? $r->status;

                        $html = '<span class="badge bg-' . $class . '">' . e($label) . '</span>';

                        if ($r->status === RosterRequest::STATUS_REJECTED && $r->rejection_reason) {
                            $html .= '<br><small class="text-muted">' . e(\Illuminate\Support\Str::limit($r->rejection_reason, 40)) . '</small>';
                        }

                        return $html;
                    }),

                TD::make('actions', 'Действия')
                    ->alignRight()
                    ->width('160px')
                    ->render(function (RosterRequest $r) {
                        if (!auth()->user()->hasAccess('platform.applications.approve') || !$r->isPending()) {
                            return '';
                        }

                        return DropDown::make()
                            ->icon('bs.three-dots-vertical')
                            ->list([
                                Button::make('Утвердить')
                                    ->icon('bs.check-lg')
                                    ->confirm('Утвердить запрос? Изменения в составе будут применены сразу.')
                                    ->method('approve', ['roster_request' => $r->id]),

                                ModalToggle::make('Отклонить')
                                    ->icon('bs.x-lg')
                                    ->modal('rejectModal')
                                    ->method('reject')
                                    ->asyncParameters(['roster_request' => $r->id]),
                            ]);
                    }),
            ]),
        ];
    }

    public function asyncGetReject(RosterRequest $roster_request): array
    {
        return [
            'roster_request_id' => $roster_request->id,
        ];
    }

    public function approve(RosterRequest $roster_request): Response
    {
        if (!auth()->user()->hasAccess('platform.applications.approve')) {
            abort(403);
        }

        if (!$roster_request->isPending()) {
            Toast::error('Запрос уже рассмотрен');
            return back();
        }

        $roster_request->load(['toApplication', 'fromApplication']);

        DB::transaction(function () use ($roster_request) {
            match ($roster_request->type) {
                RosterRequest::TYPE_ADDITION => $this->applyAddition($roster_request),
                RosterRequest::TYPE_TRANSFER => $this->applyTransfer($roster_request),
                RosterRequest::TYPE_REMOVAL => $this->applyRemoval($roster_request),
            };

            $roster_request->update([
                'status' => RosterRequest::STATUS_APPROVED,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);
        });

        Toast::success('Запрос утверждён, состав обновлён');

        return redirect()->route('platform.roster.requests.list');
    }

    public function reject(Request $request): Response
    {
        if (!auth()->user()->hasAccess('platform.applications.approve')) {
            abort(403);
        }

        $validated = $request->validate([
            'roster_request_id' => 'required|exists:roster_requests,id',
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $rosterRequest = RosterRequest::findOrFail($validated['roster_request_id']);

        if (!$rosterRequest->isPending()) {
            Toast::error('Запрос уже рассмотрен');
            return back();
        }

        $rosterRequest->update([
            'status' => RosterRequest::STATUS_REJECTED,
            'rejection_reason' => $validated['rejection_reason'] ?? null,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        Toast::info('Запрос отклонён');

        return redirect()->route('platform.roster.requests.list');
    }

    private function applyAddition(RosterRequest $request): void
    {
        if (!$request->to_application_id) {
            throw new \RuntimeException('Не указана целевая заявка');
        }

        ApplicationRoster::create([
            'application_id' => $request->to_application_id,
            'user_id' => $request->player_user_id,
            'jersey_number' => $request->jersey_number,
            'position' => $request->position,
        ]);
    }

    private function applyTransfer(RosterRequest $request): void
    {
        if (!$request->to_application_id) {
            throw new \RuntimeException('Не указана целевая заявка');
        }

        if ($request->from_application_id) {
            ApplicationRoster::where('application_id', $request->from_application_id)
                ->where('user_id', $request->player_user_id)
                ->delete();
        }

        ApplicationRoster::create([
            'application_id' => $request->to_application_id,
            'user_id' => $request->player_user_id,
            'jersey_number' => $request->jersey_number,
            'position' => $request->position,
        ]);
    }

    private function applyRemoval(RosterRequest $request): void
    {
        if (!$request->from_application_id) {
            throw new \RuntimeException('Не указана исходная заявка');
        }

        ApplicationRoster::where('application_id', $request->from_application_id)
            ->where('user_id', $request->player_user_id)
            ->delete();
    }
}
