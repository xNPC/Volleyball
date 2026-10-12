<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Broadcast;
use App\Models\StageGroup;
use App\Models\Team;
use App\Models\Tournament;
use App\Models\TournamentStage;
use App\Models\User;

class SitemapController extends Controller
{
    public function index()
    {
        $urls = collect();

        $urls->push($this->entry(route('home'), now(), 'daily', 1.0));
        $urls->push($this->entry(route('tournaments.index'), now(), 'daily', 0.9));
        $urls->push($this->entry(route('teams.index'), now(), 'daily', 0.9));
        $urls->push($this->entry(route('users.index'), now(), 'weekly', 0.8));
        $urls->push($this->entry(route('gallery.index'), now(), 'weekly', 0.7));
        $urls->push($this->entry(route('broadcasts.index'), now(), 'daily', 0.8));
        $urls->push($this->entry(route('privacy'), now(), 'yearly', 0.3));

        Tournament::query()->get()->each(function (Tournament $tournament) use ($urls) {
            $urls->push($this->entry(route('tournaments.show', $tournament->id), $tournament->updated_at, 'weekly', 0.8));
        });

        TournamentStage::query()->with('tournament')->get()->each(function (TournamentStage $stage) use ($urls) {
            if ($stage->tournament) {
                $urls->push($this->entry(route('stages.show', [$stage->tournament_id, $stage->id]), $stage->updated_at, 'weekly', 0.6));
            }
        });

        StageGroup::query()->get()->each(function (StageGroup $group) use ($urls) {
            $urls->push($this->entry(route('groups.show', $group->id), $group->updated_at, 'weekly', 0.5));
        });

        Team::query()->get()->each(function (Team $team) use ($urls) {
            $urls->push($this->entry(route('teams.show', $team->id), $team->updated_at, 'weekly', 0.7));
        });

        User::query()->get()->each(function (User $user) use ($urls) {
            $urls->push($this->entry(route('users.show', $user->id), $user->updated_at, 'weekly', 0.5));
        });

        Album::query()->where('is_active', true)->get()->each(function (Album $album) use ($urls) {
            $urls->push($this->entry(route('gallery.show', $album->slug), $album->updated_at, 'monthly', 0.5));
        });

        Broadcast::query()->where('is_active', true)->get()->each(function (Broadcast $broadcast) use ($urls) {
            $urls->push($this->entry(route('broadcasts.show', $broadcast), $broadcast->updated_at, 'daily', 0.7));
        });

        $xml = view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function robots()
    {
        $content = "User-agent: *\nDisallow:\n\nSitemap: " . url('/sitemap.xml') . "\n";

        return response($content, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    private function entry(string $loc, $lastmod, string $changefreq, float $priority): array
    {
        return [
            'loc' => $loc,
            'lastmod' => $lastmod,
            'changefreq' => $changefreq,
            'priority' => $priority,
        ];
    }
}
