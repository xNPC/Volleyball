<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class HomePage extends Component
{
    public $featuredTournaments = [
        [
            'name' => 'Кубок Победы 2024',
            'teams_count' => 16,
            'location' => 'Москва',
            'date' => '15-20 мая 2024',
            'prize' => '100,000 руб.'
        ],
        [
            'name' => 'Летний Чемпионат',
            'teams_count' => 12,
            'location' => 'Санкт-Петербург',
            'date' => '10-15 июля 2024',
            'prize' => '75,000 руб.'
        ],
        [
            'name' => 'Осенняя Лига',
            'teams_count' => 8,
            'location' => 'Казань',
            'date' => '20-25 сентября 2024',
            'prize' => '50,000 руб.'
        ]
    ];

    public $upcomingMatches = [
        [
            'team1' => 'Спартак',
            'team2' => 'Динамо',
            'time' => '18:00',
            'date' => 'Сегодня',
            'court' => 'Центральный'
        ],
        [
            'team1' => 'Локомотив',
            'team2' => 'Зенит',
            'time' => '20:00',
            'date' => 'Завтра',
            'court' => 'СпортХолл'
        ]
    ];

    public $topTeams = [
        ['name' => 'Спартак', 'wins' => 12, 'logo' => 'S'],
        ['name' => 'Динамо', 'wins' => 11, 'logo' => 'Д'],
        ['name' => 'Локомотив', 'wins' => 10, 'logo' => 'Л'],
        ['name' => 'Зенит', 'wins' => 9, 'logo' => 'З']
    ];

    public function render()
    {
        return view('livewire.home-page');
    }
}
