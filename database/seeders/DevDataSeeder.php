<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DevDataSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command->warn('DevDataSeeder пропущен: не local-окружение.');
            return;
        }

        $this->call([
            OrganizationSeeder::class,
            UserSeeder::class,
            TeamSeeder::class,
            TournamentSeeder::class,
            ContentSeeder::class,
        ]);
    }
}
