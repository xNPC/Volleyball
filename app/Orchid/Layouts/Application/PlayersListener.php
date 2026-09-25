<?php

namespace App\Orchid\Layouts\Application;

use Illuminate\Http\Request;
use Orchid\Screen\Layouts\Listener;
use Orchid\Screen\Repository;

class PlayersListener extends Listener
{
    /**
     * List of field names for which values will be listened.
     *
     * @var string[]
     */
    protected $targets = [
        'application.tournament_id'
    ];

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    protected function layouts(): iterable
    {
        $applicationId = $this->query->get('application.id');
        $isComplete = (bool) $this->query->get('application.is_complete');

        $canAddPlayer = empty($applicationId)
            || auth()->user()->hasAccess('platform.applications.edit')
            || !$isComplete;

        if (!$canAddPlayer) {
            return [];
        }

        return [
            new AddPlayerLayout(),
        ];
    }

    /**
     * Update state
     *
     * @param \Orchid\Screen\Repository $repository
     * @param \Illuminate\Http\Request  $request
     *
     * @return \Orchid\Screen\Repository
     */
    public function handle(Repository $repository, Request $request): Repository
    {
        $appTourId = $request->input('application.tournament_id');

        if (is_null($appTourId)) {
            $appTourId = $repository->get('application.team_id');
        }

        $repository->set('application.tournament_id', $appTourId);

        return $repository;
    }
}