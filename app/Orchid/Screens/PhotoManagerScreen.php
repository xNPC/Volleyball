<?php

namespace App\Orchid\Screens;

use App\Models\Album;
use App\Models\Photo;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Picture;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class PhotoManagerScreen extends Screen
{
    public ?Album $album = null;

    public function query(Album $album = null): iterable
    {
        $this->album = $album;

        if (!$this->album) {
            return [
                'album' => null,
                'photos' => [],
            ];
        }

        return [
            'album' => $this->album,
            'photos' => $this->album->photos()->orderBy('sort_order')->paginate(20),
        ];
    }

    public function name(): ?string
    {
        return $this->album && $this->album->exists ? 'Фотоальбом: ' . $this->album->title : 'Управление фото';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Назад к альбомам')
                ->icon('arrow-left')
                ->route('platform.album.list'),
        ];
    }

    public function layout(): iterable
    {
        if (!$this->album || !$this->album->exists) {
            return [
                Layout::rows([
                    Input::make('error')->title('Ошибка')->value('Альбом не найден'),
                ]),
            ];
        }

        return [
            Layout::rows([
                Group::make([
                    Input::make('photos[]')
                        ->type('file')
                        ->title('Выберите фото')
                        ->accept('image/*')
                        ->multiple(),
                ]),
                Button::make('Загрузить фото')
                    ->method('uploadPhotos')
                    ->icon('cloud-upload'),
            ])->title('Загрузка фотографий'),

            Layout::table('photos', [
                TD::make('id', 'ID')->width('50px'),
                TD::make('thumbnail', 'Превью')
                    ->width('100px')
                    ->render(function (Photo $photo) {
                        // Если ссылка есть — выводим нормальный тег img, если нет — заглушку
                        return $photo->thumbnail_url
                            ? sprintf(
                                '<img src="%s" alt="%s" class="img-fluid rounded border shadow-sm" style="width: 80px; height: 80px; object-fit: cover;">',
                                e($photo->thumbnail_url),
                                e($photo->original_name)
                            )
                            : '<span class="text-muted">Нет фото</span>';
                    }),
                TD::make('original_name', 'Название'),
                TD::make('sort_order', 'Порядок')->width('100px'),
                TD::make('created_at', 'Дата')->width('150px'),
                TD::make('actions', 'Действия')
                    ->width('100px')
                    ->render(function (Photo $photo) {
                        return Button::make('Удалить')
                            ->icon('trash')
                            ->method('deletePhoto', ['photo' => $photo->id])
                            ->confirm('Удалить фото?');
                    }),
            ]),
        ];
    }

    public function uploadPhotos(Request $request, ImageService $imageService): void
    {
        if (!$request->hasFile('photos')) {
            Alert::warning('Выберите файлы');
            return;
        }

        $files = $request->file('photos');
        if (!is_array($files)) {
            $files = [$files];
        }

        foreach ($files as $file) {
            $result = $imageService->uploadAlbumImage($file, $this->album->slug);
            Photo::create([
                'album_id' => $this->album->id,
                'original_name' => $result['original_name'],
                'filename' => $result['filename'],
                'thumbnail' => $result['thumbnail'],
                'path' => $result['path'],
                'sort_order' => Photo::where('album_id', $this->album->id)->max('sort_order') + 1,
            ]);
        }

        Alert::info('Фото загружены');
    }

    public function deletePhoto(Photo $photo, ImageService $imageService): void
    {
        $imageService->deletePhoto($photo->path, $photo->thumbnail, $this->album->slug);
        $photo->delete();
        Alert::info('Фото удалено');
    }
}
