<?php

namespace App\Services;

use App\Models\ApplicationRoster;
use App\Models\RosterRequest;
use App\Models\TournamentApplication;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RosterNumberService
{
    public function change(ApplicationRoster $roster, int $newNumber, User $actor, string $field = 'number.value'): ApplicationRoster
    {
        if ($newNumber < 1 || $newNumber > 99) {
            throw ValidationException::withMessages([
                $field => 'Игровой номер должен быть от 1 до 99.',
            ]);
        }

        return DB::transaction(function () use ($roster, $newNumber, $actor, $field) {
            $locked = ApplicationRoster::query()
                ->whereKey($roster->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $oldNumber = $locked->jersey_number;

            if ((int) $oldNumber === $newNumber) {
                return $locked;
            }

            ApplicationRoster::query()
                ->where('application_id', $locked->application_id)
                ->lockForUpdate()
                ->get();

            $this->assertAvailable(
                (int) $locked->application_id,
                $newNumber,
                (int) $locked->getKey(),
                $field
            );

            $locked->jersey_number = $newNumber;
            $locked->save();

            $tournamentId = TournamentApplication::query()
                ->whereKey($locked->application_id)
                ->value('tournament_id');

            RosterRequest::create([
                'type' => RosterRequest::TYPE_NUMBER_CHANGE,
                'tournament_id' => $tournamentId,
                'player_user_id' => $locked->user_id,
                'to_application_id' => $locked->application_id,
                'from_application_id' => null,
                'jersey_number' => (string) $newNumber,
                'old_jersey_number' => $oldNumber !== null ? (string) $oldNumber : null,
                'position' => $locked->position,
                'comment' => null,
                'status' => RosterRequest::STATUS_APPROVED,
                'created_by' => $actor->id,
                'reviewed_by' => $actor->id,
                'reviewed_at' => now(),
            ]);

            return $locked;
        });
    }

    public function assertAvailable(int $applicationId, int $number, ?int $exceptRosterId = null, string $field = 'number.value'): void
    {
        $exists = ApplicationRoster::query()
            ->where('application_id', $applicationId)
            ->whereNull('deleted_at')
            ->where('jersey_number', $number)
            ->when($exceptRosterId !== null, fn ($query) => $query->where('id', '!=', $exceptRosterId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                $field => 'Этот игровой номер уже занят в заявке.',
            ]);
        }
    }
}
