<?php

namespace App\Orchid\Filters;

use App\Models\TournamentApplication;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Select;

class ApplicationCompleteFilter extends Filter
{
    public function name(): string
    {
        return 'Завершённость';
    }

    public function parameters(): ?array
    {
        return ['is_complete'];
    }

    public function run(Builder $builder): Builder
    {
        return $builder->where('is_complete', $this->request->get('is_complete'));
    }

    public function display(): iterable
    {
        return [
            Select::make('is_complete')
                ->options(TournamentApplication::IS_COMPLETE)
                ->empty()
                ->value($this->request->get('is_complete'))
                ->set('style', 'min-width: 300px')
                ->title('Завершённость'),
        ];
    }

    public function value(): string
    {
        $isComplete = $this->request->get('is_complete');

        return 'Завершённость: '.(TournamentApplication::IS_COMPLETE[$isComplete] ?? $isComplete);
    }
}