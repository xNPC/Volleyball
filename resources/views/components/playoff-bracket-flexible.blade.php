@if (empty($bracket))
    <x-card class="p-6 text-amber-700">
        Нет данных сетки для группы {{ $group->name ?? '?' }}
    </x-card>
@endif

<div class="flex gap-8 overflow-x-auto pb-2">
    @forelse ($bracket as $roundIndex => $round)
        <div class="min-w-[340px] rounded-xl bg-slate-100 p-4" data-round="{{ $round['round_number'] ?? $roundIndex + 1 }}">
            <div class="mb-5 flex items-center justify-between border-b-2 border-accent-500 pb-3">
                <h4 class="font-display text-lg font-bold text-brand-800">
                    {{ $round['round_name'] ?? ($roundIndex === 0 ? 'Первый раунд' : 'Раунд ' . ($roundIndex + 1)) }}
                </h4>
            </div>

            <div class="flex flex-col gap-4">
                @forelse (($round['matches'] ?? []) as $match)
                    @php
                        $matchFormat = $match['match_format'] ?? 'single';
                        $games = $match['games'] ?? [];
                        $homeWins = $match['home_wins'] ?? 0;
                        $awayWins = $match['away_wins'] ?? 0;
                        $status = $match['status'] ?? 'pending';
                        $winner = $match['winner'] ?? null;

                        $homeTeamName = 'TBD';
                        $awayTeamName = 'TBD';

                        if (!empty($match['home_team'])) {
                            $homeTeamName = is_array($match['home_team'])
                                ? ($match['home_team']['name'] ?? 'TBD')
                                : ($match['home_team']->name ?? 'TBD');
                        }
                        if (!empty($match['away_team'])) {
                            $awayTeamName = is_array($match['away_team'])
                                ? ($match['away_team']['name'] ?? 'TBD')
                                : ($match['away_team']->name ?? 'TBD');
                        }

                        $statusBadge = match ($status) {
                            'completed' => ['green', 'Завершен'],
                            'scheduled' => ['amber', 'Запланирован'],
                            default => ['slate', 'Ожидание'],
                        };
                    @endphp

                    <div class="rounded-xl bg-white p-3.5 shadow-card"
                         data-match="{{ $match['match_number'] ?? $loop->index + 1 }}">
                        <div class="mb-2.5 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                            <span class="font-bold text-brand-800">#{{ $match['match_number'] ?? $loop->index + 1 }}</span>
                            @if (isset($match['title']))
                                <span class="font-bold uppercase text-accent-500">{{ $match['title'] }}</span>
                            @endif
                            @if ($matchFormat === 'best_of_3')
                                <x-badge variant="amber">До 2 побед</x-badge>
                            @endif
                            <x-badge :variant="$statusBadge[0]">{{ $statusBadge[1] }}</x-badge>
                        </div>

                        <div class="my-2.5 flex items-center justify-center gap-4">
                            <div class="flex min-w-[120px] items-center gap-2 rounded-lg px-2 py-1 {{ $winner === 'home' ? 'bg-emerald-50 font-bold' : 'bg-slate-100' }}">
                                <span class="truncate">{{ $homeTeamName }}</span>
                                <span class="ml-auto font-display text-xl font-bold text-accent-500">{{ $homeWins }}</span>
                            </div>
                            <span class="text-lg font-bold text-slate-400">:</span>
                            <div class="flex min-w-[120px] items-center gap-2 rounded-lg px-2 py-1 {{ $winner === 'away' ? 'bg-emerald-50 font-bold' : 'bg-slate-100' }}">
                                <span class="font-display text-xl font-bold text-accent-500">{{ $awayWins }}</span>
                                <span class="truncate">{{ $awayTeamName }}</span>
                            </div>
                        </div>

                        @if (count($games) > 0)
                            <div class="mt-3 space-y-1.5 border-t border-slate-100 pt-2.5 text-xs">
                                @foreach ($games as $gameIndex => $game)
                                    @php
                                        $gameHomeName = 'TBD';
                                        $gameAwayName = 'TBD';

                                        if (!empty($game['home_team'])) {
                                            $gameHomeName = is_array($game['home_team'])
                                                ? ($game['home_team']['name'] ?? 'TBD')
                                                : ($game['home_team']->name ?? 'TBD');
                                        }
                                        if (!empty($game['away_team'])) {
                                            $gameAwayName = is_array($game['away_team'])
                                                ? ($game['away_team']['name'] ?? 'TBD')
                                                : ($game['away_team']->name ?? 'TBD');
                                        }
                                        $isFinished = $game['home_score'] !== null && $game['away_score'] !== null;
                                    @endphp
                                    <div class="flex flex-wrap items-center gap-2 rounded-lg px-2 py-1.5 {{ $isFinished ? 'bg-brand-50/60' : '' }}">
                                        <span class="min-w-[56px] text-slate-500">Игра {{ $gameIndex + 1 }}</span>
                                        <span class="flex items-center gap-1.5">
                                            <span class="font-medium text-brand-900">{{ $gameHomeName }}</span>
                                            <span class="text-slate-400">vs</span>
                                            <span class="font-medium text-brand-900">{{ $gameAwayName }}</span>
                                        </span>
                                        @if ($isFinished)
                                            <span class="ml-auto font-bold text-brand-800">{{ $game['home_score'] }}:{{ $game['away_score'] }}</span>
                                            @if (!empty($game['sets']))
                                                <span class="text-slate-400">
                                                    ({{ collect($game['sets'])->map(fn($s) => $s['home_score'] . ':' . $s['away_score'])->implode(', ') }})
                                                </span>
                                            @endif
                                        @else
                                            <span class="ml-auto text-slate-400">Ожидание</span>
                                        @endif
                                    </div>
                                @endforeach

                                @php
                                    $playedGames = array_filter($games, fn($g) => $g['home_score'] !== null && $g['away_score'] !== null);
                                    $playedCount = count($playedGames);
                                    $isDraw = ($homeWins == $awayWins);
                                @endphp

                                @if ($matchFormat === 'best_of_3' && $playedCount == 2 && $isDraw && $status !== 'completed' && $status !== 'scheduled')
                                    <div class="flex items-center gap-2 rounded-lg bg-accent-500/10 px-2 py-1.5">
                                        <span class="min-w-[56px] font-semibold text-accent-600">Решающая игра</span>
                                        <span class="text-amber-600">Ожидание</span>
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if (!empty($match['next_match']))
                            <p class="mt-2.5 border-t border-dashed border-slate-200 pt-2 text-xs text-slate-500">
                                &rarr; Победитель идет в:
                                @if (($match['next_match']['type'] ?? '') === 'champion')
                                    <strong class="text-brand-800">Чемпион</strong>
                                @else
                                    Матч {{ $match['next_match']['match'] ?? '?' }}
                                    ({{ ($match['next_match']['position'] ?? '') === 'home' ? 'хозяева' : 'гости' }})
                                @endif
                            </p>
                        @endif
                    </div>
                @empty
                    <div class="rounded-xl bg-white p-4 text-center text-slate-500">Нет матчей в этом раунде</div>
                @endforelse
            </div>
        </div>
    @empty
        <div class="w-full rounded-xl bg-slate-50 p-6 text-center text-slate-500">Сетка плейофф еще не сгенерирована</div>
    @endforelse
</div>