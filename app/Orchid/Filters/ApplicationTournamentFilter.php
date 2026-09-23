<?php

namespace App\Orchid\Filters;

use App\Models\Tournament;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Select;

class ApplicationTournamentFilter extends Filter
{
    public function name(): string
    {
        return 'Турнир';
    }

    public function parameters(): ?array
    {
        return ['tournament_id'];
    }

    public function run(Builder $builder): Builder
    {
        return $builder->where('tournament_id', $this->request->get('tournament_id'));
    }

    public function display(): iterable
    {
        return [
            Select::make('tournament_id')
                ->fromModel(Tournament::class, 'name', 'id')
                ->empty()
                ->value($this->request->get('tournament_id'))
                ->set('style', 'min-width: 300px')
                ->title('Турнир'),
        ];
    }

    public function value(): string
    {
        $id = $this->request->get('tournament_id');
        $name = Tournament::find($id)?->name;

        return 'Турнир: '.($name ?? $id);
    }
}