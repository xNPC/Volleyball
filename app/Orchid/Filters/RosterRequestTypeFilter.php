<?php

namespace App\Orchid\Filters;

use App\Models\RosterRequest;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Select;

class RosterRequestTypeFilter extends Filter
{
    public function name(): string
    {
        return 'Тип';
    }

    public function parameters(): ?array
    {
        return ['type'];
    }

    public function run(Builder $builder): Builder
    {
        return $builder->when($this->request->filled('type'), function (Builder $builder) {
            $builder->where('type', $this->request->get('type'));
        });
    }

    public function display(): iterable
    {
        return [
            Select::make('type')
                ->options(RosterRequest::TYPES)
                ->empty()
                ->value($this->request->get('type'))
                ->set('style', 'min-width: 300px')
                ->title('Тип'),
        ];
    }

    public function value(): string
    {
        $type = $this->request->get('type');

        return 'Тип: ' . (RosterRequest::TYPES[$type] ?? $type);
    }
}
