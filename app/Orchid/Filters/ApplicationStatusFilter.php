<?php

namespace App\Orchid\Filters;

use App\Models\TournamentApplication;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Select;

class ApplicationStatusFilter extends Filter
{
    public function name(): string
    {
        return 'Статус';
    }

    public function parameters(): ?array
    {
        return ['status'];
    }

    public function run(Builder $builder): Builder
    {
        return $builder->where('status', $this->request->get('status'));
    }

    public function display(): iterable
    {
        return [
            Select::make('status')
                ->options(TournamentApplication::STATUS)
                ->empty()
                ->value($this->request->get('status'))
                ->set('style', 'min-width: 300px')
                ->title('Статус'),
        ];
    }

    public function value(): string
    {
        $status = $this->request->get('status');

        return 'Статус: '.(TournamentApplication::STATUS[$status] ?? $status);
    }
}