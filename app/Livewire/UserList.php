<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class UserList extends Component
{
    use WithPagination;

    #[Url]
    public $search = '';

    #[Url]
    public $filter = 'all';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilter()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filter']);
        $this->resetPage();
    }

    public function render()
    {
        $users = User::withCount([
            'approvedTournamentApplications as approved_applications_count',
            'tournamentApplications as total_applications_count'
        ])
            ->when($this->search, function($query) {
                $query->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            })
            ->when($this->filter === 'with_teams', function($query) {
                $query->has('approvedTournamentApplications');
            })
            ->when($this->filter === 'new', function($query) {
                $query->where('created_at', '>=', now()->subMonth());
            })
            ->when($this->filter === 'verified', function($query) {
                $query->whereNotNull('email_verified_at');
            })
            ->orderBy('name')
            ->paginate(16);

        return view('livewire.user-list', [
            'users' => $users,
            'stats' => [
                'total_users' => User::count(),
                'new_users_month' => User::where('created_at', '>=', now()->subMonth())->count(),
                'users_with_applications' => User::has('approvedTournamentApplications')->count(),
                'verified_users' => User::whereNotNull('email_verified_at')->count(),
            ],
        ]);
    }
}