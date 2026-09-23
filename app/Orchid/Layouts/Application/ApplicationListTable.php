<?php

namespace App\Orchid\Layouts\Application;

use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use App\Models\Tournament;
use App\Models\TournamentApplication;

class ApplicationListTable extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'applications';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('tournament_id', 'Турнир')
                ->filter(Select::make()
                    ->options(Tournament::orderBy('name')->pluck('name', 'id')->all())
                    ->empty('Все турниры'))
                ->filterValue(fn ($value) => Tournament::find($value)?->name ?? $value)
                ->render(fn ($application) => $application->tournament?->name ?? '—'),
            TD::make('team.name', 'Команда'),
            //TD::make('venue.name', 'Домашний зал'),
            TD::make('status', 'Статус')
                ->filter(Select::make()
                    ->options(TournamentApplication::STATUS)
                    ->empty('Все статусы'))
                ->filterValue(fn ($value) => TournamentApplication::STATUS[$value] ?? $value)
                ->render(function ($application) {
                    $statusText = $application::STATUS[$application->status] ?? 'Неизвестно';
                    $color = match($application->status) {
                        'pending' => 'warning',    // в ожидании - оранжевый
                        'approved' => 'success',   // утверждена - зеленый
                        'rejected' => 'danger',    // отклонена - красный
                        default => 'secondary'
                    };

                    return "<span class='badge bg-{$color}'>{$statusText}</span>";
                }),

            TD::make('is_complete', 'Завершена')
                ->filter(Select::make()
                    ->options(TournamentApplication::IS_COMPLETE)
                    ->empty('Все'))
                ->filterValue(fn ($value) => TournamentApplication::IS_COMPLETE[$value] ?? $value)
                ->render(function ($application) {
                    $completeText = $application::IS_COMPLETE[$application->is_complete] ?? 'Неизвестно';

                    if ($application->is_complete) {
                        return "<span class='badge bg-success fw-bold'>{$completeText}</span>";
                    } else {
                        return "<span class='badge bg-secondary'>{$completeText}</span>";
                    }
                }),
            TD::make('created_at', 'Дата создания')
                ->render(function ($application) {
                    return $application->created_at->format('d.m.Y H:i:s');
                }),
            TD::make('updated_at', 'Дата обновления')
                ->render(function ($application) {
                    return $application->updated_at->format('d.m.Y H:i:s');
                }),
            TD::make('Действия')
                ->render(fn ($application) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        Link::make('Редактировать')
                            ->icon('pencil')
                            ->route('platform.applications.edit', $application),
                        Button::make('Удалить')
                            ->method('remove', ['application' => $application])
                            ->icon('trash')
                        ->canSee(auth()->user()->hasAccess('platform.applications.delete')),
                    ])
                )
        ];
    }
}
