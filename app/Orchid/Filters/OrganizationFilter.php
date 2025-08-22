<?php

namespace App\Orchid\Filters;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Input;
use Orchid\Support\Facades\Toast;

class OrganizationFilter extends Filter
{
    /**
     * The displayable name of the filter.
     *
     * @return string
     */
    public function name(): string
    {
        return 'Организация';
    }

//    public function key(): string
//    {
//        return 'organization.name';
//    }

    /**
     * The array of matched parameters.
     *
     * @return array|null
     */
    public function parameters(): ?array
    {
        return [
            'organization'
        ];
    }

    /**
     * Apply to a given Eloquent query builder.
     *
     * @param Builder $builder
     *
     * @return Builder
     */
    public function run(Builder $builder): Builder
    {
        if ($this->request->has('organization')) {

            return $builder->whereHas('organization', function (Builder $query) {

                $query->where('name', 'like', '%' . $this->request->get('organization') . '%');
            });
        }

        return $builder;
    }

    /**
     * Get the display fields.
     *
     * @return Field[]
     */
    public function display(): iterable
    {
        return [
            Input::make('organization')
                ->type('text')
                ->title('Название организации')
                ->placeholder('Введите название организации...')
                ->value($this->request->get('organization')),
        ];
    }
}
