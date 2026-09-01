<?php

namespace App\Orchid\Screens\Documentation;

use App\Models\Documentation;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Quill;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Symfony\Component\HttpFoundation\Response;

class DocumentationEditScreen extends Screen
{
    public $doc;

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
    public function query(Documentation $doc = null): iterable
    {
        $this->doc = $doc ?? new Documentation();

        return [
            'doc' => $this->doc,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->doc->exists ? 'Редактирование инструкции' : 'Создание инструкции';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        $commands = [
            Button::make('Сохранить')
                ->icon('bs.check-lg')
                ->method('save'),
        ];

        if ($this->doc->exists) {
            $commands[] = Button::make('Удалить')
                ->icon('bs.trash')
                ->type(Color::DANGER)
                ->confirm('Вы уверены, что хотите удалить инструкцию? Эта операция отмене не подлежит!')
                ->method('remove');
        }

        return $commands;
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('doc.title')
                    ->type('text')
                    ->title('Вопрос')
                    ->placeholder('Например: Как подать заявку на турнир?')
                    ->required(),

                Group::make([
                    Input::make('doc.order')
                        ->type('number')
                        ->title('Порядок отображения')
                        ->value($this->doc->order ?? (int) Documentation::max('order') + 1)
                        ->min(0)
                        ->required(),

                    Switcher::make('doc.status')
                        ->title('Включена')
                        ->sendTrueOrFalse(),
                ])
                    ->autoWidth(),

                Quill::make('doc.content')
                    ->title('Текст инструкции')
                    ->height('500px'),

                Group::make([
                    Button::make('Сохранить')
                        ->icon('bs.check-lg')
                        ->type(Color::PRIMARY)
                        ->method('save'),
                    Button::make('Выйти')
                        ->icon('bs.box-arrow-right')
                        ->type(Color::BASIC)
                        ->confirm('Вы уверены, что хотите выйти? Все несохраненные изменения пропадут!')
                        ->novalidate()
                        ->method('close'),
                ])
                    ->autoWidth(),
            ]),
        ];
    }

    public function save(Request $request): Response
    {
        $request->validate([
            'doc.title'   => 'required|string|max:255',
            'doc.order'   => 'required|integer|min:0',
            'doc.status'  => 'nullable|boolean',
            'doc.content' => 'nullable|string',
        ]);

        $data = $request->input('doc', []);

        if ($this->doc->exists) {
            $this->doc->update($data);
            Alert::info('Инструкция обновлена');
        } else {
            Documentation::create($data);
            Alert::info('Инструкция создана');
        }

        return redirect()->route('platform.documentation.list');
    }

    public function remove(): Response
    {
        $this->doc->delete();
        Alert::info('Инструкция удалена');

        return redirect()->route('platform.documentation.list');
    }

    public function close(): Response
    {
        return redirect()->route('platform.documentation.list');
    }
}
