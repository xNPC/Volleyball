<?php

namespace App\Livewire;

use App\Models\Game;
use App\Models\Tournament;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class GameList extends Component
{
    use WithPagination;

    #[Url]
    public $search = '';

    #[Url]
    public $tournaments = [];

    #[Url]
    public $period = 'all';

    #[Url]
    public $status = 'all';

    #[Url]
    public $dateFrom = '';

    #[Url]
    public $dateTo = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedTournaments()
    {
        $this->resetPage();
    }

    public function updatedPeriod()
    {
        $this->resetPage();
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }

    public function updatedDateFrom()
    {
        $this->resetPage();
    }

    public function updatedDateTo()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'tournaments', 'period', 'status', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function render()
    {
        $tournamentIds = collect($this->tournaments)
            ->filter(fn ($id) => filled($id))
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $games = Game::query()
            ->with([
                'homeApplication.team',
                'awayApplication.team',
                'stage.tournament',
                'venue',
                'sets',
            ])
            ->when($this->search, function ($query) {
                $search = $this->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('homeApplication.team', function ($t) use ($search) {
                        $t->where('name', 'like', "%{$search}%");
                    })->orWhereHas('awayApplication.team', function ($t) use ($search) {
                        $t->where('name', 'like', "%{$search}%");
                    });
                });
            })
            ->when(!empty($tournamentIds), function ($query) use ($tournamentIds) {
                $query->whereHas('stage', function ($q) use ($tournamentIds) {
                    $q->whereIn('tournament_id', $tournamentIds);
                });
            })
            ->when($this->period === 'upcoming', function ($query) {
                $query->whereNotNull('scheduled_time')->where('scheduled_time', '>=', now());
            })
            ->when($this->period === 'past', function ($query) {
                $query->whereNotNull('scheduled_time')->where('scheduled_time', '<', now());
            })
            ->when($this->status !== 'all', function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->dateFrom, function ($query) {
                $query->whereDate('scheduled_time', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                $query->whereDate('scheduled_time', '<=', $this->dateTo);
            })
            ->when($this->period === 'upcoming', function ($query) {
                $query->orderBy('scheduled_time');
            }, function ($query) {
                $query->orderByRaw('scheduled_time is null')->orderByDesc('scheduled_time');
            })
            ->paginate(30);

        $tournamentsList = Tournament::query()
            ->whereHas('stages.games')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.game-list', [
            'games' => $games,
            'tournamentsList' => $tournamentsList,
            'statuses' => Game::STATUSES,
        ]);
    }
}
