<?php

namespace App\Orchid\Screens\Documentation;

use App\Models\Documentation;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Symfony\Component\HttpFoundation\Response;

class DocumentationListScreen extends Screen
{
    public function permission(): ?iterable
    {
        return [
            'platform.content.docs',
        ];
    }

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'docs' => Documentation::query()->orderBy('order')->get(),
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
        return [
            Link::make('Создать инструкцию')
                ->icon('bs.plus-lg')
                ->route('platform.documentation.create'),
        ];
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
                    ->width('100px')
                    ->sort(),

                TD::make('title', 'Вопрос')
                    ->sort()
                    ->filter(TD::FILTER_TEXT),

                TD::make('content', 'Превью')
                    ->render(fn (Documentation $doc) => Str::limit(trim(strip_tags($doc->content ?? '')), 100)),

                TD::make('status', 'Статус')
                    ->width('100px')
                    ->sort()
                    ->render(function (Documentation $doc) {
                        return $doc->status ? '✅' : '❌';
                    }),

                TD::make('Действия')
                    ->alignCenter()
                    ->width('120px')
                    ->render(fn (Documentation $doc) => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make('Редактировать')
                                ->icon('bs.pencil')
                                ->route('platform.documentation.edit', ['doc' => $doc]),
                            Button::make('Удалить')
                                ->icon('bs.trash')
                                ->confirm('Вы уверены, что хотите удалить? Эта операция отмене не подлежит!')
                                ->method('remove', ['doc' => $doc->id]),
                        ])),
            ]),
        ];
    }

    public function remove(Documentation $doc): Response
    {
        $doc->delete();
        Alert::info('Инструкция удалена');

        return redirect()->route('platform.documentation.list');
    }
}
