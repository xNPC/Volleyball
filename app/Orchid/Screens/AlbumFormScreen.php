<?php

namespace App\Orchid\Screens;

use App\Models\Album;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Orchid\Attachment\Models\Attachment;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Symfony\Component\HttpFoundation\Response;

class AlbumFormScreen extends Screen
{
    public $album;

    public function query(Album $album = null): iterable
    {
        // Подгружаем стандартную связь cover, которую мы настроили в модели Album
        $this->album = $album && $album->exists ? $album->load('cover') : new Album();

        return [
            'album' => $this->album,
            // Передаем модель обложки Orchid, если она привязана
            'cover' => $this->album->cover ? collect([$this->album->cover]) : collect([]),
        ];
    }


    public function name(): ?string
    {
        return $this->album->exists ? 'Редактировать альбом' : 'Создать альбом';
    }

    public function commandBar(): iterable
    {
        $commands = [
            Button::make('Сохранить')
                ->icon('save')
                ->method('save'),
        ];

        if ($this->album->exists) {
            $commands[] = Button::make('Удалить')
                ->icon('trash')
                ->method('delete')
                ->confirm('Удалить альбом? Все фото будут удалены!');
        }

        return $commands;
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('album.title')
                    ->title('Название')
                    ->required()
                    ->placeholder('Введите название альбома'),

                Input::make('album.slug')
                    ->title('Slug (URL)')
                    ->placeholder('Оставьте пустым для автоматической генерации')
                    ->help('Будет автоматически сгенерирован из названия, если оставить пустым'),

                TextArea::make('album.description')
                    ->title('Описание')
                    ->rows(5)
                    ->placeholder('Описание альбома'),

                Switcher::make('album.is_active')
                    ->title('Активен')
                    ->sendTrueOrFalse(),

                Input::make('album.sort_order')
                    ->title('Сортировка')
                    ->type('number')
                    ->placeholder('0')
                    ->value('0'),

                Upload::make('cover')
                    ->title('Обложка альбома')
                    ->acceptedFiles('image/*')
                    ->maxFiles(1)
                    ->groups('album_cover')
                    ->storage('public'),
            ]),
        ];
    }

    public function save(Request $request, ImageService $imageService): Response
    {
        // 1. Получаем основные текстовые поля (title, slug, description, etc.)
        $data = $request->get('album', []);

        // Генерация slug из названия, если поле пустое
        if (empty($data['slug'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['title']);
        }

        // Проверка уникальности slug
        $slug = $data['slug'];
        $originalSlug = $slug;
        $counter = 1;
        $query = Album::where('slug', $slug);
        if ($this->album->exists) {
            $query->where('id', '!=', $this->album->id);
        }
        while ($query->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $query = Album::where('slug', $slug);
            if ($this->album->exists) {
                $query->where('id', '!=', $this->album->id);
            }
            $counter++;
        }
        $data['slug'] = $slug;

        // 2. ОБРАБОТКА ОБЛОЖКИ: Получаем ID из изолированного поля 'cover'
        $coverIds = $request->input('cover');

        if (!empty($coverIds) && is_array($coverIds)) {
            // Записываем СВЕЖИЙ ID в массив данных для сохранения в базу
            $data['cover_image'] = head($coverIds);
        } else {
            // Если обложку удалили в интерфейсе крестиком
            $data['cover_image'] = null;
        }

        // 3. СОХРАНЕНИЕ В БАЗУ ДАННЫХ
        if ($this->album->exists) {
            // Сохраняем старый slug для переименования папки
            $oldSlug = $this->album->slug;

            // Обновляем альбом новыми данными (включая свежий cover_image!)
            $this->album->update($data);

            // Если slug изменился, переименовываем папку с фото
            if ($oldSlug !== $slug) {
                $oldPath = storage_path('app/public/gallery/' . $oldSlug);
                $newPath = storage_path('app/public/gallery/' . $slug);

                if (file_exists($oldPath)) {
                    rename($oldPath, $newPath);
                }

                // Обновляем пути к фото в базе данных
                foreach ($this->album->photos as $photo) {
                    $photo->thumbnail = str_replace($oldSlug, $slug, $photo->thumbnail);
                    $photo->path = str_replace($oldSlug, $slug, $photo->path);
                    $photo->save();
                }
            }

            Alert::info('Альбом обновлен');
        } else {
            // Создаем новый альбом сразу со свежим cover_image
            $this->album = Album::create($data);
            Alert::info('Альбом создан');
        }

        // 4. СИНХРОНИЗАЦИЯ С ТАБЛИЦЕЙ ORCHID
// Передаем чистый массив ID без указания дополнительных колонок (вроде group)
        if (!empty($coverIds)) {
            $this->album->attachments()->sync($coverIds);
        } else {
            $this->album->attachments()->detach();
        }

        return redirect()->route('platform.album.list');
    }

    public function delete(ImageService $imageService): Response
    {
        // Удаляем все фото через сервис
        $imageService->deleteAlbumImages($this->album->slug);

        // Удаляем альбом
        $this->album->delete();

        Alert::info('Альбом удален');
        return redirect()->route('platform.album.list');
    }
}
