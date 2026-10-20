<?php

namespace App\Orchid\Screens\LegalDocument;

use App\Models\LegalDocument;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Quill;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Symfony\Component\HttpFoundation\Response;

class LegalDocumentEditScreen extends Screen
{
    public $document;

    public function permission(): ?iterable
    {
        return [
            'platform.content.legal',
        ];
    }

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(string $slug = null): iterable
    {
        $slug = $slug ?: request()->route('slug');

        abort_unless(in_array($slug, LegalDocument::SLUGS, true), 404);

        $this->document = LegalDocument::findBySlug($slug)
            ?? new LegalDocument(['slug' => $slug]);

        return [
            'document' => $this->document,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        if ($this->document->title) {
            return $this->document->title;
        }

        return $this->document->slug === LegalDocument::TERMS
            ? 'Пользовательское соглашение'
            : 'Согласие на обработку персональных данных';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Сохранить')
                ->icon('bs.check-lg')
                ->method('save'),
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
            Layout::rows([
                Input::make('document.title')
                    ->type('text')
                    ->title('Заголовок')
                    ->required(),

                Quill::make('document.content')
                    ->title('Текст документа')
                    ->height('600px'),

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
            'document.title'   => 'required|string|max:255',
            'document.content' => 'nullable|string',
        ]);

        $data = $request->input('document', []);

        if ($this->document->exists) {
            $this->document->update($data);
        } else {
            $this->document->forceFill($data)->save();
        }

        Alert::info('Документ сохранён');

        return redirect()->route('platform.legal.edit', $this->document->slug);
    }

    public function close(): Response
    {
        return redirect()->route('platform.index');
    }
}
