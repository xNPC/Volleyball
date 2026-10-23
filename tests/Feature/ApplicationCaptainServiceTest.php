<?php

namespace Tests\Feature;

use App\Models\ApplicationRoster;
use App\Models\Organization;
use App\Models\RosterRequest;
use App\Models\Team;
use App\Models\Tournament;
use App\Models\TournamentApplication;
use App\Models\User;
use App\Services\ApplicationCaptainService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ApplicationCaptainServiceTest extends TestCase
{
    use RefreshDatabase;

    private function createCaptainChangeFixture(string $tournamentStatus = 'ongoing'): array
    {
        $organization = Organization::create([
            'name' => 'Тестовая организация',
            'contact_email' => 'captain-test@example.com',
            'contact_phone' => '+7 000 000-00-00',
        ]);

        $tournament = Tournament::create([
            'organization_id' => $organization->id,
            'name' => 'Тестовый турнир',
            'volleyball_type' => 'indoor',
            'start_date' => '2026-03-10',
            'end_date' => '2026-03-15',
            'status' => $tournamentStatus,
        ]);

        $oldCaptain = User::create([
            'name' => 'Старый капитан',
            'email' => 'old-captain@example.com',
            'password' => 'password',
        ]);

        $newCaptain = User::create([
            'name' => 'Новый капитан',
            'email' => 'new-captain@example.com',
            'password' => 'password',
        ]);

        $otherPlayer = User::create([
            'name' => 'Другой игрок',
            'email' => 'other-player@example.com',
            'password' => 'password',
        ]);

        $team = Team::create([
            'name' => 'Тестовая команда',
            'captain_id' => $oldCaptain->id,
        ]);

        $application = TournamentApplication::create([
            'tournament_id' => $tournament->id,
            'team_id' => $team->id,
            'status' => 'approved',
        ]);

        $oldCaptainRoster = ApplicationRoster::create([
            'application_id' => $application->id,
            'user_id' => $oldCaptain->id,
            'jersey_number' => 1,
            'position' => 'outside',
            'is_captain' => true,
        ]);

        $newCaptainRoster = ApplicationRoster::create([
            'application_id' => $application->id,
            'user_id' => $newCaptain->id,
            'jersey_number' => 7,
            'position' => 'setter',
            'is_captain' => false,
        ]);

        $otherRoster = ApplicationRoster::create([
            'application_id' => $application->id,
            'user_id' => $otherPlayer->id,
            'jersey_number' => 10,
            'position' => 'libero',
            'is_captain' => false,
        ]);

        return compact(
            'tournament',
            'team',
            'application',
            'oldCaptain',
            'newCaptain',
            'otherPlayer',
            'oldCaptainRoster',
            'newCaptainRoster',
            'otherRoster'
        );
    }

    public function test_it_creates_pending_captain_change_request(): void
    {
        [
            'application' => $application,
            'oldCaptain' => $oldCaptain,
            'newCaptain' => $newCaptain,
            'oldCaptainRoster' => $oldCaptainRoster,
        ] = $this->createCaptainChangeFixture();

        $request = app(ApplicationCaptainService::class)->createRequest(
            $application,
            $newCaptain,
            $oldCaptain,
            'Нужно заменить капитана'
        );

        $this->assertSame(RosterRequest::TYPE_CAPTAIN_CHANGE, $request->type);
        $this->assertSame(RosterRequest::STATUS_PENDING, $request->status);
        $this->assertSame($oldCaptainRoster->user_id, $request->old_player_user_id);
        $this->assertSame($newCaptain->id, $request->player_user_id);
        $this->assertSame($application->id, $request->to_application_id);
        $this->assertSame($oldCaptain->id, $request->created_by);
    }

    public function test_it_rejects_player_not_in_roster(): void
    {
        [
            'application' => $application,
            'oldCaptain' => $oldCaptain,
        ] = $this->createCaptainChangeFixture();

        $outsider = User::create([
            'name' => 'Посторонний игрок',
            'email' => 'outsider@example.com',
            'password' => 'password',
        ]);

        $this->expectException(ValidationException::class);

        app(ApplicationCaptainService::class)->createRequest($application, $outsider, $oldCaptain);
    }

    public function test_it_rejects_current_captain(): void
    {
        [
            'application' => $application,
            'oldCaptain' => $oldCaptain,
        ] = $this->createCaptainChangeFixture();

        $this->expectException(ValidationException::class);

        app(ApplicationCaptainService::class)->createRequest($application, $oldCaptain, $oldCaptain);
    }

    public function test_it_rejects_duplicate_pending_request(): void
    {
        [
            'application' => $application,
            'oldCaptain' => $oldCaptain,
            'newCaptain' => $newCaptain,
        ] = $this->createCaptainChangeFixture();

        $service = app(ApplicationCaptainService::class);
        $service->createRequest($application, $newCaptain, $oldCaptain);

        $this->expectException(ValidationException::class);

        $service->createRequest($application, $newCaptain, $oldCaptain);
    }

    public function test_it_rejects_request_after_tournament_completed(): void
    {
        [
            'application' => $application,
            'oldCaptain' => $oldCaptain,
            'newCaptain' => $newCaptain,
            'tournament' => $tournament,
        ] = $this->createCaptainChangeFixture();

        $tournament->update(['status' => 'completed']);

        $this->expectException(ValidationException::class);

        app(ApplicationCaptainService::class)->createRequest($application, $newCaptain, $oldCaptain);
    }

    public function test_it_applies_captain_change(): void
    {
        [
            'application' => $application,
            'oldCaptain' => $oldCaptain,
            'newCaptain' => $newCaptain,
            'oldCaptainRoster' => $oldCaptainRoster,
            'newCaptainRoster' => $newCaptainRoster,
            'otherRoster' => $otherRoster,
        ] = $this->createCaptainChangeFixture();

        $service = app(ApplicationCaptainService::class);
        $request = $service->createRequest($application, $newCaptain, $oldCaptain);

        $service->apply($request);

        $this->assertFalse($oldCaptainRoster->refresh()->is_captain);
        $this->assertTrue($newCaptainRoster->refresh()->is_captain);
        $this->assertFalse($otherRoster->refresh()->is_captain);
        $this->assertSame(
            1,
            ApplicationRoster::where('application_id', $application->id)
                ->where('is_captain', true)
                ->count()
        );
    }

    public function test_it_stores_null_old_captain_when_roster_has_no_captain(): void
    {
        [
            'application' => $application,
            'oldCaptain' => $oldCaptain,
            'newCaptain' => $newCaptain,
            'oldCaptainRoster' => $oldCaptainRoster,
        ] = $this->createCaptainChangeFixture();

        $oldCaptainRoster->update(['is_captain' => false]);

        $request = app(ApplicationCaptainService::class)->createRequest($application, $newCaptain, $oldCaptain);

        $this->assertNull($request->old_player_user_id);
    }
}
