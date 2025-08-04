<?php
namespace App\Orchid\Controllers;

use App\Models\Mmatch;
use Illuminate\Http\Request;

class MatchController
{
    public function save(Request $request, Mmatch $match)
    {
        $match->fill($request->input('match'))->save();

        // Сохраняем сеты
        $match->sets()->delete();
        foreach ($request->input('match.sets', []) as $setData) {
            $match->sets()->create($setData);
        }

        // Обновляем общий счет
        $match->update([
            'home_score' => $match->sets->sum('home_score'),
            'away_score' => $match->sets->sum('away_score'),
            'status' => 'completed'
        ]);

        return redirect()->route('platform.matches.list');
    }
}
