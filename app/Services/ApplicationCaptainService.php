<?php

namespace App\Services;

use App\Models\ApplicationRoster;
use App\Models\RosterRequest;
use App\Models\TournamentApplication;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApplicationCaptainService
{
    public function createRequest(
        TournamentApplication $application,
        User $newCaptain,
        User $actor,
        ?string $comment = null
    ): RosterRequest {
        return DB::transaction(function () use ($application, $newCaptain, $actor, $comment) {
            $application = TournamentApplication::query()
                ->whereKey($application->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($application->status !== 'approved') {
                throw ValidationException::withMessages([
                    'captain' => 'Заявка должна быть утверждена.',
                ]);
            }

            if ($application->tournament?->status === 'completed') {
                throw ValidationException::withMessages([
                    'captain' => 'Турнир завершён, смена капитана недоступна.',
                ]);
            }

            $roster = ApplicationRoster::query()
                ->where('application_id', $application->id)
                ->lockForUpdate()
                ->get();

            $newCaptainRoster = $roster->firstWhere('user_id', $newCaptain->id);

            if (!$newCaptainRoster) {
                throw ValidationException::withMessages([
                    'captain' => 'Игрок не найден в составе этой заявки.',
                ]);
            }

            if ((bool) $newCaptainRoster->is_captain) {
                throw ValidationException::withMessages([
                    'captain' => 'Этот игрок уже является капитаном заявки.',
                ]);
            }

            $hasPendingRequest = RosterRequest::query()
                ->where('to_application_id', $application->id)
                ->where('type', RosterRequest::TYPE_CAPTAIN_CHANGE)
                ->where('status', RosterRequest::STATUS_PENDING)
                ->exists();

            if ($hasPendingRequest) {
                throw ValidationException::withMessages([
                    'captain' => 'По этой заявке уже есть запрос на смену капитана, ожидающий рассмотрения.',
                ]);
            }

            $oldCaptainRoster = $roster->first(fn (ApplicationRoster $row) => (bool) $row->is_captain);

            return RosterRequest::create([
                'type' => RosterRequest::TYPE_CAPTAIN_CHANGE,
                'tournament_id' => $application->tournament_id,
                'player_user_id' => $newCaptain->id,
                'old_player_user_id' => $oldCaptainRoster?->user_id,
                'to_application_id' => $application->id,
                'from_application_id' => null,
                'jersey_number' => null,
                'old_jersey_number' => null,
                'position' => null,
                'comment' => $comment,
                'status' => RosterRequest::STATUS_PENDING,
                'created_by' => $actor->id,
            ]);
        });
    }

    public function apply(RosterRequest $request): void
    {
        if ($request->type !== RosterRequest::TYPE_CAPTAIN_CHANGE) {
            throw new \RuntimeException('Запрос не является сменой капитана');
        }

        if (!$request->to_application_id) {
            throw new \RuntimeException('Не указана целевая заявка');
        }

        DB::transaction(function () use ($request) {
            $roster = ApplicationRoster::query()
                ->where('application_id', $request->to_application_id)
                ->lockForUpdate()
                ->get();

            $newCaptainRoster = $roster->firstWhere('user_id', $request->player_user_id);

            if (!$newCaptainRoster) {
                throw ValidationException::withMessages([
                    'captain' => 'Игрок не найден в составе заявки.',
                ]);
            }

            ApplicationRoster::query()
                ->where('application_id', $request->to_application_id)
                ->update(['is_captain' => false]);

            $newCaptainRoster->is_captain = true;
            $newCaptainRoster->save();
        });
    }
}
