<?php

namespace App\Orchid\Screens\Documentation;

use App\Models\Documentation;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class DocumentationListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'docs' => Documentation::all()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Создание и редактирование инструкций';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::table('docs', [
                TD::make('order', 'Порядок')
                    ->width('100px'),
                TD::make('title', 'Вопрос'),
                TD::make('status', 'Статус')
                    ->width('100px')
                    ->render(function (Documentation $doc) {
                        return $doc->status ? '✅' : '❌';
                    }),
                TD::make('Действия')
                    ->render(fn(Documentation $doc) => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make('Редактировать')
                                ->icon('bs.pencil')
                                ->route('platform.documentation.edit', ['doc' => $doc]),
                            Button::make('Удалить')
                                ->icon('bs.trash')
                                ->confirm('Вы уверены, что хотите удалить? Эта операция отмене не подлежит!')
                        ]),
                    )
            ])
        ];
    }
}
