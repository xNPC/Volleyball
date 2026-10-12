@if ($broadcast->game)
    <div class="mt-4 flex flex-wrap items-center gap-4 text-sm">
        <span class="flex items-center gap-2 font-medium text-brand-900">
            <x-team-logo :name="$broadcast->game->homeApplication->team->name ?? '?'" size="sm" />
            {{ $broadcast->game->homeApplication->team->name ?? 'Не определено' }}
        </span>
        <span class="rounded-lg border border-slate-200 px-2 py-0.5 font-bold text-brand-800">
            @if ($broadcast->game->home_score !== null && $broadcast->game->away_score !== null)
                {{ $broadcast->game->home_score }}:{{ $broadcast->game->away_score }}
            @else
                vs
            @endif
        </span>
        <span class="flex items-center gap-2 font-medium text-brand-900">
            {{ $broadcast->game->awayApplication->team->name ?? 'Не определено' }}
            <x-team-logo :name="$broadcast->game->awayApplication->team->name ?? '?'" size="sm" />
        </span>
    </div>
@endif
