<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageService
{
    protected ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * Загрузка изображения для альбома
     */
    public function uploadAlbumImage(UploadedFile $file, string $albumSlug): array
    {
        $albumPath = 'gallery/' . $albumSlug;

        // Уникальное имя файла
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        // Сохраняем оригинал
        $originalPath = $file->storeAs($albumPath . '/originals', $filename, 'public');

        // Создаем превью 300x300
        $image = $this->manager->read($file->getPathname());
        $image->cover(300, 300);
        $thumbnailPath = $albumPath . '/thumbnails/' . $filename;
        Storage::disk('public')->put($thumbnailPath, $image->toJpeg(85));

        // Создаем версию для просмотра (макс 1200px)
        $viewImage = $this->manager->read($file->getPathname());
        $viewImage->scaleDown(width: 1200);
        $viewPath = $albumPath . '/views/' . $filename;
        Storage::disk('public')->put($viewPath, $viewImage->toJpeg(85));

        return [
            'original_name' => $file->getClientOriginalName(),
            'filename' => $filename,
            'path' => $viewPath,
            'thumbnail' => $thumbnailPath,
        ];
    }

    /**
     * Удаление всего альбома с фото
     */
    public function deleteAlbumImages(string $albumSlug): void
    {
        $albumPath = 'gallery/' . $albumSlug;

        if (Storage::disk('public')->exists($albumPath)) {
            Storage::disk('public')->deleteDirectory($albumPath);
        }

    }

    /**
     * Удаление одного фото
     */
    public function deletePhoto(string $path, string $thumbnail, string $albumSlug): void
    {
        Storage::disk('public')->delete($path);
        Storage::disk('public')->delete($thumbnail);

        // Удаляем оригинал
        $filename = basename($path);
        Storage::disk('public')->delete('gallery/' . $albumSlug . '/originals/' . $filename);
    }
}
