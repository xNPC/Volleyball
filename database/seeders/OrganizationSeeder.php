<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Venue;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $org1 = Organization::updateOrCreate(
            ['name' => 'Федерация волейбола России'],
            [
                'description' => 'Всероссийская общественная организация, занимающаяся развитием волейбола.',
                'contact_email' => 'info@volleyball.ru',
                'contact_phone' => '+7 (495) 123-45-67',
            ]
        );

        $org2 = Organization::updateOrCreate(
            ['name' => 'Московская федерация волейбола'],
            [
                'description' => 'Региональная федерация волейбола города Москвы.',
                'contact_email' => 'info@moscow-volleyball.ru',
                'contact_phone' => '+7 (495) 765-43-21',
            ]
        );

        $venues = [
            ['name' => 'Спорткомплекс «Олимпийский»', 'address' => 'ул. Ленина, 10'],
            ['name' => 'Дворец спорта «Динамо»', 'address' => 'пр. Мира, 25'],
            ['name' => 'Спортзал №1', 'address' => 'ул. Советская, 5'],
            ['name' => 'ФОК «Здоровье»', 'address' => 'ул. Парковая, 15'],
            ['name' => 'Спорткомплекс «Юность»', 'address' => 'ул. Школьная, 3'],
        ];

        foreach ($venues as $index => $venue) {
            Venue::updateOrCreate(
                ['name' => $venue['name'], 'organization_id' => $org1->id],
                ['address' => $venue['address']]
            );
        }

        Venue::updateOrCreate(
            ['name' => 'Спортзал «Сокол»', 'organization_id' => $org2->id],
            ['address' => 'ул. Победы, 7']
        );
    }
}
