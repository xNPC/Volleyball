<?php

namespace App\Orchid\Screens\Broadcast;

use App\Models\Broadcast;
use App\Models\Game;
use App\Models\Tournament;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Symfony\Component\HttpFoundation\Response;

class BroadcastFormScreen extends Screen
{
    public $broadcast;

    public $games = [];

    public function permission(): ?iterable
    {
        return [
            'platform.content.broadcasts',
        ];
    }

    public function query(Broadcast $broadcast = null): iterable
    {
        $this->broadcast = $broadcast && $broadcast->exists
            ? $broadcast->load('cover')
            : new Broadcast();

        $this->games = Game::query()
            ->with(['homeApplication.team', 'awayApplication.team', 'stage.tournament'])
            ->where(function ($query) {
                $query->where('scheduled_time', '>=', now()->subDays(14));

                if ($this->broadcast->game_id) {
                    $query->orWhere('id', $this->broadcast->game_id);
                }
            })
            ->orderBy('scheduled_time')
            ->limit(200)
            ->get()
            ->mapWithKeys(function (Game $game) {
                $home = optional(optional($game->homeApplication)->team)->name;
                $away = optional(optional($game->awayApplication)->team)->name;
                $date = $game->scheduled_time ? $game->scheduled_time->format('d.m.Y H:i') : '';

                return [$game->id => trim($date . ' — ' . $home . ' — ' . $away, ' —')];
            })
            ->all();

        $scheduledAt = $this->broadcast->scheduled_at;

        return [
            'broadcast'               => $this->broadcast,
            'broadcast.scheduled_date' => $scheduledAt ? $scheduledAt->format('d.m.Y') : null,
            'broadcast.scheduled_time' => $scheduledAt ? $scheduledAt->format('H:i') : null,
            'preview'                 => $this->broadcast->preview_image && $this->broadcast->cover
                ? collect([$this->broadcast->cover])
                : collect([]),
        ];
    }

    public function name(): ?string
    {
        return $this->broadcast->exists ? 'Редактирование трансляции' : 'Создание трансляции';
    }

    public function commandBar(): iterable
    {
        $commands = [
            Button::make('Сохранить')
                ->icon('bs.check-lg')
                ->method('save'),
        ];

        if ($this->broadcast->exists) {
            $commands[] = Button::make('Удалить')
                ->icon('bs.trash')
                ->type(Color::DANGER)
                ->confirm('Удалить трансляцию? Действие необратимо!')
                ->method('remove');
        }

        return $commands;
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('broadcast.title')
                    ->title('Название')
                    ->placeholder('Например: Финал. Матч за 1 место')
                    ->required(),

                TextArea::make('broadcast.description')
                    ->title('Описание')
                    ->rows(3),

                Group::make([
                    Select::make('broadcast.tournament_id')
                        ->title('Турнир')
                        ->fromModel(Tournament::class, 'name')
                        ->empty('Без привязки к турниру'),

                    Select::make('broadcast.game_id')
                        ->title('Игра (необязательно)')
                        ->options($this->games)
                        ->empty('Без привязки к игре'),
                ])
                    ->autoWidth(),

                Input::make('broadcast.vk_url')
                    ->title('Ссылка на трансляцию ВК')
                    ->placeholder('https://vk.com/video-123456_789012345')
                    ->help('Ссылка на видео или эфир из ВКонтакте'),

                Input::make('broadcast.embed_url')
                    ->title('Ссылка на плеер (если нужна отдельная)')
                    ->placeholder('https://vk.com/video_ext.php?oid=...&id=...')
                    ->help('Необязательно. Если пусто — соберём автоматически из ссылки ВК'),

                TextArea::make('broadcast.embed_code')
                    ->title('Код вставки (iframe)')
                    ->rows(3)
                    ->help('Необязательно. Используется, если ВК выдаёт готовый код эфира'),

                Group::make([
                    DateTimer::make('broadcast.scheduled_date')
                        ->title('Дата начала')
                        ->format('d.m.Y')
                        ->allowInput()
                        ->placeholder('Выберите дату'),

                    DateTimer::make('broadcast.scheduled_time')
                        ->title('Время начала')
                        ->enableTime()
                        ->noCalendar()
                        ->format('H:i')
                        ->format24hr()
                        ->allowInput()
                        ->placeholder('Выберите время'),
                ])
                    ->autoWidth(),

                Group::make([
                    Select::make('broadcast.status')
                        ->title('Статус')
                        ->options(Broadcast::STATUSES)
                        ->required(),

                    Switcher::make('broadcast.is_active')
                        ->title('Активна')
                        ->sendTrueOrFalse(),

                    Input::make('broadcast.sort_order')
                        ->title('Сортировка')
                        ->type('number')
                        ->value('0'),
                ])
                    ->autoWidth(),

                Upload::make('preview')
                    ->title('Превью (заставка)')
                    ->acceptedFiles('image/*')
                    ->maxFiles(1)
                    ->groups('broadcast_preview')
                    ->storage('public'),
            ]),
        ];
    }

    public function save(Request $request): Response
    {
        $request->validate([
            'broadcast.title'         => 'required|string|max:255',
            'broadcast.tournament_id' => 'nullable|integer|exists:tournaments,id',
            'broadcast.game_id'       => 'nullable|integer|exists:games,id',
            'broadcast.vk_url'        => 'nullable|string|max:255',
            'broadcast.embed_url'     => 'nullable|string|max:255',
            'broadcast.status'        => 'required|in:' . implode(',', array_keys(Broadcast::STATUSES)),
            'broadcast.scheduled_date' => 'nullable|date_format:d.m.Y',
            'broadcast.scheduled_time' => 'nullable|date_format:H:i',
            'preview'                 => 'nullable|array',
        ]);

        $data = $request->input('broadcast', []);

        $scheduledDate = $request->input('broadcast.scheduled_date');
        $scheduledTime = $request->input('broadcast.scheduled_time');

        if ($scheduledDate && $scheduledTime) {
            $data['scheduled_at'] = \Carbon\Carbon::createFromFormat('d.m.Y H:i', $scheduledDate . ' ' . $scheduledTime);
        } else {
            $data['scheduled_at'] = null;
        }

        unset($data['scheduled_date'], $data['scheduled_time']);

        foreach (['tournament_id', 'game_id'] as $field) {
            if (empty($data[$field])) {
                $data[$field] = null;
            }
        }

        if (empty($data['sort_order'])) {
            $data['sort_order'] = 0;
        }

        $previewIds = $request->input('preview');
        $data['preview_image'] = ! empty($previewIds) && is_array($previewIds) ? head($previewIds) : null;

        if ($this->broadcast->exists) {
            $this->broadcast->update($data);
            Alert::info('Трансляция обновлена');
        } else {
            $this->broadcast = Broadcast::create($data);
            Alert::info('Трансляция создана');
        }

        if (! empty($previewIds) && is_array($previewIds)) {
            $this->broadcast->attachments()->sync($previewIds);
        } else {
            $this->broadcast->attachments()->detach();
        }

        return redirect()->route('platform.broadcasts.list');
    }

    public function remove(): Response
    {
        $this->broadcast->delete();
        Alert::info('Трансляция удалена');

        return redirect()->route('platform.broadcasts.list');
    }
}
