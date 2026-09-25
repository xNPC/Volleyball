<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\ImageService;
use Illuminate\Console\Command;

class ProfilePhotoThumbnails extends Command
{
    protected $signature = 'app:profile-photo-thumbnails';

    protected $description = 'Предгенерация миниатюр профильных фотографий игроков';

    public function handle(ImageService $imageService): int
    {
        $users = User::whereNotNull('profile_photo_path')->get();

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            $imageService->profilePhotoThumbnail($user);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Обработано пользователей: ' . $users->count());

        return self::SUCCESS;
    }
}