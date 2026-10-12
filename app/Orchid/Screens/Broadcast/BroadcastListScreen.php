<?php

namespace App\Orchid\Screens\Broadcast;

use App\Models\Broadcast;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Symfony\Component\HttpFoundation\Response;

class BroadcastListScreen extends Screen
{
    public function permission(): ?iterable
    {
        return [
            'platform.content.broadcasts',
        ];
    }

    public function query(): iterable
    {
        return [
            'broadcasts' => Broadcast::query()
                ->with(['tournament', 'game.homeApplication.team', 'game.awayApplication.team'])
                ->orderByDesc('scheduled_at')
                ->orderBy('sort_order')
                ->paginate(20),
        ];
    }

    public function name(): ?string
    {
        return 'Трансляции';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Создать трансляцию')
                ->icon('bs.plus-lg')
                ->route('platform.broadcasts.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('broadcasts', [
                TD::make('id', 'ID')
                    ->sort()
                    ->width('80px'),

                TD::make('title', 'Название')
                    ->render(fn (Broadcast $broadcast) => Link::make($broadcast->title)
                        ->route('platform.broadcasts.edit', $broadcast)),

                TD::make('tournament', 'Турнир')
                    ->render(fn (Broadcast $broadcast) => optional($broadcast->tournament)->name ?? '—'),

                TD::make('game', 'Игра')
                    ->render(function (Broadcast $broadcast) {
                        if (! $broadcast->game) {
                            return '—';
                        }

                        return $broadcast->game->home_team->name . ' — ' . $broadcast->game->away_team->name;
                    }),

                TD::make('status', 'Статус')
                    ->alignCenter()
                    ->sort()
                    ->render(fn (Broadcast $broadcast) => Broadcast::STATUSES[$broadcast->status] ?? $broadcast->status),

                TD::make('scheduled_at', 'Дата и время')
                    ->sort()
                    ->render(fn (Broadcast $broadcast) => optional($broadcast->scheduled_at)->format('d.m.Y H:i') ?? '—'),

                TD::make('is_active', 'Активна')
                    ->alignCenter()
                    ->render(fn (Broadcast $broadcast) => $broadcast->is_active ? '✅' : '❌'),

                TD::make('Действия')
                    ->alignCenter()
                    ->width('120px')
                    ->render(fn (Broadcast $broadcast) => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make('Редактировать')
                                ->icon('bs.pencil')
                                ->route('platform.broadcasts.edit', $broadcast),
                            Button::make('Удалить')
                                ->icon('bs.trash')
                                ->confirm('Удалить трансляцию? Действие необратимо!')
                                ->method('remove', ['broadcast' => $broadcast->id]),
                        ])),
            ]),
        ];
    }

    public function remove(Broadcast $broadcast): Response
    {
        $broadcast->delete();
        Alert::info('Трансляция удалена');

        return redirect()->route('platform.broadcasts.list');
    }
}
