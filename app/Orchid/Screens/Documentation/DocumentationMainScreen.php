<?php

namespace App\Orchid\Screens\Documentation;

use App\Models\Documentation;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
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
        $this->docs = Documentation::query()
            ->where('status', 1)
            ->orderBy('order')
            ->get();

        return [
            'docs' => $this->docs,
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
        if (! auth()->user()?->hasAccess('platform.content.docs')) {
            return [];
        }

        return [
//            Link::make('Редактировать')
//                ->icon('bs.pencil')
//                ->route('platform.documentation.list'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        $docs = collect($this->docs);

        if ($docs->isEmpty()) {
            return [
                Layout::view('orchid.documentation.empty'),
            ];
        }

        $accordionItems = [];

        foreach ($docs as $doc) {
            $accordionItems[$doc->order . '. ' . $doc->title] = [
                Layout::view('orchid.documentation.item', ['doc' => $doc]),
            ];
        }

        return [
            Layout::view('orchid.documentation.style'),
            Layout::accordion($accordionItems)->open([]),
        ];
    }
}
