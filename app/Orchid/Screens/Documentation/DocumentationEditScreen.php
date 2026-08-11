<?php

namespace App\Orchid\Screens\Documentation;

use App\Models\Documentation;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Quill;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;

class DocumentationEditScreen extends Screen
{

    public $documentation;
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Documentation $doc): iterable
    {
        $this->doc = $doc;

        return [
            'doc' => $doc,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->doc ? 'Редактирование вопроса' : 'Создание вопроса';
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

            Layout::rows([
                Input::make('doc.title')
                    ->type('text')
                    ->title('Вопрос')
                    ->required(),
                Group::make([
                    Input::make('doc.order')
                        ->type('number')
                        ->title('Порядок отображения')
                        ->value($this->doc->order ?? 0)
                        ->min(0)
                        ->required(),
                    Switcher::make('doc.status')
                        ->title('Включение'),
                ])
                    ->autoWidth(),
                Quill::make('doc.content')
                    ->height('500px'),
                Group::make([
                    Button::make('Сохранить')
                        ->type(Color::PRIMARY),
                    Button::make('Выйти')
                        ->type(Color::BASIC)
                        ->confirm('Вы уверены, что хотите выйти? Все несохраненные изменения пропадут!')
                        ->method('close')
                ])
                    ->autoWidth(),
            ])

        ];
    }

    public function close()
    {
        return redirect()->route('platform.documentation.list');
    }
}
