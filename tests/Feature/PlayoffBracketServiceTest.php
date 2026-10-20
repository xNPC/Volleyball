<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Organization;
use App\Models\PlayoffConfig;
use App\Models\StageGroup;
use App\Models\Team;
use App\Models\Tournament;
use App\Models\TournamentApplication;
use App\Models\TournamentStage;
use App\Models\Venue;
use App\Services\PlayoffBracketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlayoffBracketServiceTest extends TestCase
{
    use RefreshDatabase;

    private function createTournamentWithTeams(int $teamCount): array
    {
        $organization = Organization::create([
            'name' => 'Тестовая организация',
            'contact_email' => 'test@example.com',
            'contact_phone' => '+7 000 000-00-00',
        ]);

        $tournament = Tournament::create([
            'organization_id' => $organization->id,
            'name' => 'Тестовый турнир',
            'volleyball_type' => 'indoor',
            'start_date' => '2026-03-10',
            'end_date' => '2026-03-15',
            'status' => 'ongoing',
        ]);

        $applications = [];
        for ($i = 1; $i <= $teamCount; $i++) {
            $team = Team::create(['name' => "Команда {$i}"]);
            $applications[] = TournamentApplication::create([
                'tournament_id' => $tournament->id,
                'team_id' => $team->id,
                'status' => 'approved',
            ]);
        }

        $stage = TournamentStage::create([
            'tournament_id' => $tournament->id,
            'name' => 'Плейофф',
            'stage_type' => 'playoff',
            'order' => 1,
            'start_date' => '2026-03-13',
            'end_date' => '2026-03-15',
        ]);

        $group = StageGroup::create([
            'stage_id' => $stage->id,
            'name' => 'Плейофф',
            'order' => 1,
            'team_count' => $teamCount,
            'is_active' => true,
        ]);

        foreach ($applications as $index => $application) {
            $group->teams()->attach($application->id, ['position' => $index + 1]);
        }

        return [$tournament, $stage, $group, $applications];
    }

    public function test_four_team_bracket_has_semifinal_and_final_without_empty_slots(): void
    {
        [, $stage, $group, $applications] = $this->createTournamentWithTeams(4);

        PlayoffConfig::create([
            'stage_id' => $stage->id,
            'group_id' => $group->id,
            'total_teams' => 4,
            'bye_positions' => [],
            'match_format' => 'single',
        ]);

        $venue = Venue::create([
            'name' => 'Тестовый зал',
            'address' => 'г. Тестовый, ул. Тестовая, д. 1',
            'organization_id' => $group->stage->tournament->organization_id,
        ]);

        // Полуфиналы: 1 vs 4, 2 vs 3.
        Game::create([
            'stage_id' => $stage->id,
            'group_id' => $group->id,
            'home_application_id' => $applications[0]->id,
            'away_application_id' => $applications[3]->id,
            'venue_id' => $venue->id,
            'scheduled_time' => '2026-03-13 10:00:00',
            'status' => 'completed',
            'home_score' => 3,
            'away_score' => 1,
        ]);
        Game::create([
            'stage_id' => $stage->id,
            'group_id' => $group->id,
            'home_application_id' => $applications[1]->id,
            'away_application_id' => $applications[2]->id,
            'venue_id' => $venue->id,
            'scheduled_time' => '2026-03-13 12:00:00',
            'status' => 'completed',
            'home_score' => 3,
            'away_score' => 2,
        ]);

        $config = PlayoffConfig::where('stage_id', $stage->id)->first();

        $bracket = app(PlayoffBracketService::class)->generateBracket($group->fresh(), $config->toArray());

        $this->assertCount(2, $bracket, 'Сетка на 4 команды должна состоять из двух раундов');

        $semifinal = $bracket[0];
        $this->assertSame('1/2 финала', $semifinal['round_name']);
        $this->assertCount(2, $semifinal['matches']);
        $this->assertSame(1, $semifinal['matches'][0]['home_team']['position']);
        $this->assertSame(4, $semifinal['matches'][0]['away_team']['position']);
        $this->assertSame(2, $semifinal['matches'][1]['home_team']['position']);
        $this->assertSame(3, $semifinal['matches'][1]['away_team']['position']);

        $finals = $bracket[1];
        $this->assertSame(2, $finals['round_number']);
        $this->assertCount(2, $finals['matches']);

        $final = $finals['matches'][0];
        $this->assertSame('Финал', $final['title']);
        $this->assertNotNull($final['home_team'], 'В финале должен быть хозяин');
        $this->assertNotNull($final['away_team'], 'В финале должен быть гость (не пустое место)');
        $this->assertSame(1, $final['home_team']['position']);
        $this->assertSame(2, $final['away_team']['position']);

        $thirdPlace = $finals['matches'][1];
        $this->assertSame('Матч за 3-е место', $thirdPlace['title']);
        $this->assertNotNull($thirdPlace['home_team']);
        $this->assertNotNull($thirdPlace['away_team']);
        $this->assertSame(4, $thirdPlace['home_team']['position']);
        $this->assertSame(3, $thirdPlace['away_team']['position']);
    }

    public function test_eight_team_bracket_collapses_to_three_rounds(): void
    {
        [, $stage, $group, $applications] = $this->createTournamentWithTeams(8);

        PlayoffConfig::create([
            'stage_id' => $stage->id,
            'group_id' => $group->id,
            'total_teams' => 8,
            'bye_positions' => [],
            'match_format' => 'single',
        ]);

        $config = PlayoffConfig::where('stage_id', $stage->id)->first();

        $bracket = app(PlayoffBracketService::class)->generateBracket($group->fresh(), $config->toArray());

        $this->assertCount(3, $bracket, 'Сетка на 8 команд должна состоять из трёх раундов');
        $this->assertSame('1/4 финала', $bracket[0]['round_name']);
        $this->assertSame('1/2 финала', $bracket[1]['round_name']);
        $this->assertSame(3, $bracket[2]['round_number']);
        $this->assertSame('Финал', $bracket[2]['matches'][0]['title']);
        $this->assertSame('Матч за 3-е место', $bracket[2]['matches'][1]['title']);
    }

    public function test_six_team_bracket_with_two_byes_starts_with_two_quarterfinal_matches(): void
    {
        [, $stage, $group] = $this->createTournamentWithTeams(6);

        PlayoffConfig::create([
            'stage_id' => $stage->id,
            'group_id' => $group->id,
            'total_teams' => 6,
            'bye_positions' => [1, 2],
            'match_format' => 'single',
        ]);

        $config = PlayoffConfig::where('stage_id', $stage->id)->first();

        $bracket = app(PlayoffBracketService::class)->generateBracket($group->fresh(), $config->toArray());

        $this->assertCount(3, $bracket);
        $this->assertSame('1/4 финала', $bracket[0]['round_name']);
        $this->assertSame('1/2 финала', $bracket[1]['round_name']);

        $quarterfinal = $bracket[0];
        $this->assertCount(4, $quarterfinal['matches'], 'Сетка дополняется до 8 слотов');

        // BYE у посевов 1 и 2; реально играют 4v5 и 3v6.
        $this->assertSame(1, $quarterfinal['matches'][0]['home_team']['position']);
        $this->assertNull($quarterfinal['matches'][0]['away_team']);
        $this->assertSame(4, $quarterfinal['matches'][1]['home_team']['position']);
        $this->assertSame(5, $quarterfinal['matches'][1]['away_team']['position']);
        $this->assertSame(2, $quarterfinal['matches'][2]['home_team']['position']);
        $this->assertNull($quarterfinal['matches'][2]['away_team']);
        $this->assertSame(3, $quarterfinal['matches'][3]['home_team']['position']);
        $this->assertSame(6, $quarterfinal['matches'][3]['away_team']['position']);

        $this->assertSame(3, $bracket[2]['round_number']);
        $this->assertSame('Финал', $bracket[2]['matches'][0]['title']);
        $this->assertSame('Матч за 3-е место', $bracket[2]['matches'][1]['title']);
    }

    public function test_seven_team_bracket_with_one_bye_starts_with_three_quarterfinal_matches(): void
    {
        [, $stage, $group] = $this->createTournamentWithTeams(7);

        PlayoffConfig::create([
            'stage_id' => $stage->id,
            'group_id' => $group->id,
            'total_teams' => 7,
            'bye_positions' => [1],
            'match_format' => 'single',
        ]);

        $config = PlayoffConfig::where('stage_id', $stage->id)->first();

        $bracket = app(PlayoffBracketService::class)->generateBracket($group->fresh(), $config->toArray());

        $this->assertCount(3, $bracket);
        $this->assertSame('1/4 финала', $bracket[0]['round_name']);
        $this->assertSame('1/2 финала', $bracket[1]['round_name']);

        $quarterfinal = $bracket[0];
        $this->assertCount(4, $quarterfinal['matches'], 'Сетка дополняется до 8 слотов');

        // BYE у посева 1; реально играют 4v5, 2v7 и 3v6.
        $this->assertSame(1, $quarterfinal['matches'][0]['home_team']['position']);
        $this->assertNull($quarterfinal['matches'][0]['away_team']);
        $this->assertSame(4, $quarterfinal['matches'][1]['home_team']['position']);
        $this->assertSame(5, $quarterfinal['matches'][1]['away_team']['position']);
        $this->assertSame(2, $quarterfinal['matches'][2]['home_team']['position']);
        $this->assertSame(7, $quarterfinal['matches'][2]['away_team']['position']);
        $this->assertSame(3, $quarterfinal['matches'][3]['home_team']['position']);
        $this->assertSame(6, $quarterfinal['matches'][3]['away_team']['position']);

        $this->assertSame(3, $bracket[2]['round_number']);
        $this->assertSame('Финал', $bracket[2]['matches'][0]['title']);
        $this->assertSame('Матч за 3-е место', $bracket[2]['matches'][1]['title']);
    }
}
