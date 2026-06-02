<?php

namespace App\Orchid\Screens;

use App\Models\Album;
use App\Services\ImageService;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Alert;

class AlbumListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'albums' => Album::withCount('photos')->orderBy('sort_order')->paginate(20)
        ];
    }

    public function name(): ?string
    {
        return 'Фотоальбомы';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Создать альбом')
                ->icon('plus')
                ->route('platform.album.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('albums', [
                TD::make('id', 'ID')
                    ->sort()
                    ->width('100px'),

                TD::make('title', 'Название')
                    ->render(function (Album $album) {
                        return Link::make($album->title)
                            ->route('platform.album.edit', $album);
                    }),

                TD::make('photos_count', 'Кол-во фото')
                    ->alignCenter()
                    ->render(function (Album $album) {
                        return $album->photos_count ?? 0;
                    }),

                TD::make('is_active', 'Активен')
                    ->alignCenter()
                    ->render(function (Album $album) {
                        return $album->is_active ? '✅ Да' : '❌ Нет';
                    }),

                TD::make('sort_order', 'Сортировка')
                    ->alignCenter()
                    ->width('100px'),

                TD::make('created_at', 'Создан')
                    ->render(function (Album $album) {
                        return $album->created_at->format('d.m.Y');
                    }),

                TD::make('actions', 'Действия')
                    ->alignCenter()
                    ->width('100px') // Для выпадающего списка можно уменьшить ширину
                    ->render(function (Album $album) {
                        return \Orchid\Screen\Actions\DropDown::make()
                            ->icon('three-dots-vertical') // Красивая иконка трех точек
                            ->class('btn btn-link text-secondary p-0') // Стилизация кнопки вызова меню
                            ->list([
                                // 1. Кнопка управления фото
                                Link::make('Управление фото')
                                    ->icon('images')
                                    ->route('platform.album.photos', $album),

                                // 2. Кнопка редактирования
                                Link::make('Редактировать')
                                    ->icon('pencil')
                                    ->route('platform.album.edit', $album),

                                // 3. Кнопка удаления (разделим её визуально)
                                Button::make('Удалить')
                                    ->icon('trash')
                                    ->confirm('Вы уверены? Вместе с альбомом будут удалены все фото!')
                                    ->method('delete', ['album' => $album->id])
                                    //->class('text-danger'), // Подсветим удаление красным цветом
                            ]);
                    }),
            ]),
        ];
    }

    public function delete(Album $album, ImageService $imageService): void
    {
        // Удаляем папку со всеми фото альбома
        $imageService->deleteAlbumImages($album->slug);

        // Удаляем все записи фото из базы
        foreach ($album->photos as $photo) {
            $photo->delete();
        }

        // Удаляем сам альбом
        $album->delete();

        Alert::info('Альбом "' . $album->title . '" удален вместе со всеми фото');
    }
}
