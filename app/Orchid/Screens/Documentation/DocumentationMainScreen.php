<?php

namespace App\Orchid\Screens\Documentation;

use App\Models\Documentation;
use Faker\Provider\Text;
use Illuminate\Database\Eloquent\Collection;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Screen;
use Orchid\Screen\Sight;
use Orchid\Support\Facades\Layout;

class DocumentationMainScreen extends Screen
{

    public $docs = [];
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $documentations = Documentation::where('status', 1)->orderBy('order')->get();

        $this->docs = $documentations;

        return [
            'docs' => $documentations,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Документация';
    }

    public function description(): ?string
    {
        return 'Подробная инструкция, как принять участие в турнирах.';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make('Редактировать')
                ->icon('pencil')
                ->route('platform.documentation.list')
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        $accordionItems = [];

        foreach ($this->docs as $doc) {
            $accordionItems[$doc->order .'. ' . $doc->title] = [
                Layout::rows([
                    Label::make('')
                        ->value($doc->content)
                ])
            ];
        }

        return [

            Layout::accordion(
                $accordionItems
            )
                ->open(''),
        ];
    }
}
