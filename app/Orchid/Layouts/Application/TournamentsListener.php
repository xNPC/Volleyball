<?php

namespace App\Orchid\Layouts\Application;

use App\Models\Team;
use App\Models\Tournament;
use App\Models\Venue;
use Illuminate\Http\Request;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Layouts\Listener;
use Orchid\Screen\Repository;
use Orchid\Support\Facades\Layout;

class TournamentsListener extends Listener
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
        return [

            Layout::rows([

                Relation::make('application.tournament_id')
                    ->fromModel(Tournament::class, 'name')
                    ->applyScope('active')
                    ->title('Турнир')
                    ->required(),

                Relation::make('application.team_id')
                    ->fromModel(Team::class, 'name')
                    ->applyScope('userTeamsWithoutApplication', $this->query->get('application.tournament_id'))
                    ->title('Команда')
                    ->help('Если команды нет в списке, значит на нее уже создана заявка на этот турнир, либо же сама команда еще не создана!')
                    ->required(),

                Relation::make('application.venue_id')
                    ->fromModel(Venue::class, 'name')
                    ->displayAppend('display_name')
                    ->title('Домашний зал')
                    ->help('Обратите внимание! Поиск зала идет по названию, а не по адресу!')
                    ->allowEmpty()
                    ->required()
                    ->canSee((bool) $this->query->get('application.needs_venue')),
                ]),

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

        $repository->set(
            'application.needs_venue',
            (bool) Tournament::where('id', $appTourId)->value('needs_venue')
        );

        return $repository;
    }
}
