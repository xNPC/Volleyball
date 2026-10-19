<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Orchid\Platform\Dashboard;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@volleyball.local'],
            [
                'name' => 'Администратор',
                'password' => Hash::make('password'),
                'phone' => '+7 (900) 000-00-01',
                'gender' => 'male',
                'birthday' => '1985-01-01',
                'show_email' => false,
                'show_birthday' => false,
                'personal_data_consent_at' => now(),
                'email_verified_at' => now(),
            ]
        );

        $admin->forceFill(['permissions' => app(Dashboard::class)->getAllowAllPermission()]);
        $admin->save();

        $maleNames = [
            'Иван Иванов', 'Александр Петров', 'Дмитрий Сидоров', 'Сергей Козлов',
            'Андрей Новиков', 'Алексей Морозов', 'Максим Волков', 'Владимир Соколов',
            'Евгений Попов', 'Николай Лебедев', 'Павел Кузнецов', 'Роман Панов',
            'Артём Громов', 'Кирилл Орлов', 'Денис Белов', 'Олег Чернов',
            'Виктор Зайцев', 'Игорь Павлов', 'Михаил Семёнов', 'Станислав Голубев',
            'Юрий Виноградов', 'Василий Богданов', 'Григорий Воробьёв', 'Никита Фёдоров',
        ];

        $femaleNames = [
            'Анна Иванова', 'Мария Петрова', 'Елена Сидорова', 'Ольга Козлова',
            'Наталья Новикова', 'Ирина Морозова', 'Татьяна Волкова', 'Светлана Соколова',
            'Екатерина Попова', 'Анастасия Лебедева', 'Юлия Кузнецова', 'Дарья Панова',
            'Ксения Громова', 'Виктория Орлова', 'Полина Белова', 'Алина Чернова',
            'Вера Зайцева', 'Людмила Павлова', 'Галина Семёнова', 'Нина Голубева',
            'Зоя Виноградова', 'Маргарита Богданова', 'Клавдия Воробьёва', 'Раиса Фёдорова',
        ];

        foreach ($maleNames as $index => $name) {
            $this->createPlayer($name, 'male', $index);
        }

        foreach ($femaleNames as $index => $name) {
            $this->createPlayer($name, 'female', $index + 24);
        }
    }

    private function createPlayer(string $name, string $gender, int $index): void
    {
        $birthYear = rand(1985, 2005);
        $birthMonth = rand(1, 12);
        $birthDay = rand(1, 28);

        User::updateOrCreate(
            ['email' => 'player' . ($index + 1) . '@volleyball.local'],
            [
                'name' => $name,
                'password' => Hash::make('password'),
                'phone' => '+7 (9' . str_pad((string)rand(0, 99), 2, '0', STR_PAD_LEFT) . ') ' . str_pad((string)rand(100, 999), 3, '0', STR_PAD_LEFT) . '-' . str_pad((string)rand(10, 99), 2, '0', STR_PAD_LEFT) . '-' . str_pad((string)rand(10, 99), 2, '0', STR_PAD_LEFT),
                'gender' => $gender,
                'birthday' => Carbon::create($birthYear, $birthMonth, $birthDay)->format('Y-m-d'),
                'show_email' => rand(0, 1),
                'show_birthday' => rand(0, 1),
                'personal_data_consent_at' => now(),
                'email_verified_at' => now(),
            ]
        );
    }
}
