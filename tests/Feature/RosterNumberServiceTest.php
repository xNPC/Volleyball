<?php

namespace Tests\Feature;

use App\Models\ApplicationRoster;
use App\Models\Organization;
use App\Models\Team;
use App\Models\Tournament;
use App\Models\TournamentApplication;
use App\Models\User;
use App\Services\RosterNumberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RosterNumberServiceTest extends TestCase
{
    use RefreshDatabase;

    private function createRoster(int $number = 10, string $suffix = ''): array
    {
        $organization = Organization::create([
            'name' => 'Тестовая организация' . $suffix,
            'contact_email' => 'test' . $suffix . '@example.com',
            'contact_phone' => '+7 000 000-00-00',
        ]);

        $tournament = Tournament::create([
            'organization_id' => $organization->id,
            'name' => 'Тестовый турнир' . $suffix,
            'volleyball_type' => 'indoor',
            'start_date' => '2026-03-10',
            'end_date' => '2026-03-15',
            'status' => 'ongoing',
        ]);

        $team = Team::create(['name' => 'Тестовая команда' . $suffix]);

        $application = TournamentApplication::create([
            'tournament_id' => $tournament->id,
            'team_id' => $team->id,
            'status' => 'approved',
        ]);

        $player = User::create([
            'name' => 'Игрок' . $suffix,
            'email' => 'player' . $suffix . '@example.com',
            'password' => 'password',
        ]);

        $actor = User::create([
            'name' => 'Капитан' . $suffix,
            'email' => 'captain' . $suffix . '@example.com',
            'password' => 'password',
        ]);

        $roster = ApplicationRoster::create([
            'application_id' => $application->id,
            'user_id' => $player->id,
            'jersey_number' => $number,
            'position' => 'outside',
            'is_captain' => false,
        ]);

        return compact('tournament', 'team', 'application', 'player', 'actor', 'roster');
    }

    public function test_it_changes_number_and_writes_history(): void
    {
        ['application' => $application, 'player' => $player, 'actor' => $actor, 'roster' => $roster] = $this->createRoster();

        app(RosterNumberService::class)->change($roster, 7, $actor);

        $this->assertDatabaseHas('application_rosters', [
            'id' => $roster->id,
            'jersey_number' => 7,
        ]);

        $this->assertDatabaseHas('roster_requests', [
            'type' => 'number_change',
            'tournament_id' => $application->tournament_id,
            'player_user_id' => $player->id,
            'to_application_id' => $application->id,
            'old_jersey_number' => '10',
            'jersey_number' => '7',
            'status' => 'approved',
            'created_by' => $actor->id,
            'reviewed_by' => $actor->id,
        ]);
    }

    public function test_it_does_not_write_history_when_number_is_unchanged(): void
    {
        ['actor' => $actor, 'roster' => $roster] = $this->createRoster();

        app(RosterNumberService::class)->change($roster, 10, $actor);

        $this->assertDatabaseCount('roster_requests', 0);
    }

    public function test_it_rejects_number_already_used_in_application(): void
    {
        ['application' => $application, 'actor' => $actor, 'roster' => $roster] = $this->createRoster();

        $otherPlayer = User::create([
            'name' => 'Другой игрок',
            'email' => 'other@example.com',
            'password' => 'password',
        ]);

        ApplicationRoster::create([
            'application_id' => $application->id,
            'user_id' => $otherPlayer->id,
            'jersey_number' => 7,
            'position' => 'libero',
            'is_captain' => false,
        ]);

        $this->expectException(ValidationException::class);

        app(RosterNumberService::class)->change($roster, 7, $actor);
    }
}
