<x-app-layout>
    <div>
        <x-breadcrumb :items="[
            ['label' => 'Турниры', 'url' => route('tournaments.index')],
            ['label' => $tournament->name, 'url' => route('tournaments.show', $tournament)],
            ['label' => 'Команды', 'url' => route('tournaments.teams', $tournament)],
            ['label' => $team->name, 'url' => route('teams.show', $team)],
            ['label' => 'Состав'],
        ]" />

        <x-card class="mb-6 p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="font-display text-2xl font-bold tracking-tight text-brand-800 sm:text-3xl">Состав команды</h1>
                    <h2 class="mt-1 text-lg text-slate-500">{{ $team->name }} в турнире {{ $tournament->name }}</h2>
                    <div class="mt-3 flex flex-wrap gap-x-5 gap-y-1.5 text-sm text-slate-500">
                        <span class="inline-flex items-center gap-1.5">
                            @svg('lucide-users', 'h-4 w-4'){{ $roster->count() }} игроков в заявке
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            @svg('lucide-calendar-days', 'h-4 w-4')Заявка подана: {{ $application->created_at->format('d.m.Y') }}
                        </span>
                    </div>
                </div>
                <div class="shrink-0">
                    <x-team-logo :name="$team->name" size="lg" />
                </div>
            </div>
        </x-card>

        @if ($roster->count() === 0)
            <x-card class="py-14 text-center">
                <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                    @svg('lucide-users', 'h-7 w-7')
                </div>
                <h4 class="font-display text-lg font-bold text-brand-800">Состав команды пуст</h4>
                <p class="mt-1 text-slate-500">В заявке на турнир пока нет игроков</p>
            </x-card>
        @endif

        @if ($roster->count() > 0)
            <x-card class="overflow-hidden">
                <div class="px-6 pt-6">
                    <h3 class="flex items-center gap-2 font-display text-lg font-bold text-brand-800">
                        @svg('lucide-table', 'h-5 w-5 text-accent-500')Детальная информация о составе
                    </h3>
                </div>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-y border-slate-100 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                                <th class="px-6 py-3 font-semibold">Игрок</th>
                                <th class="px-6 py-3 font-semibold">Позиция</th>
                                <th class="px-6 py-3 font-semibold">Номер</th>
                                <th class="px-6 py-3 font-semibold">Дата регистрации</th>
                                <th class="px-6 py-3 font-semibold">Действия</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($roster->sortBy('jersey_number') as $rosterEntry)
                                <tr class="transition hover:bg-slate-50">
                                    <td class="px-6 py-2.5">
                                        <div class="flex items-center gap-3">
                                            <a href="{{ route('users.show', $rosterEntry->user) }}">
                                                @if ($rosterEntry->user->profile_photo_path)
                                                    <div data-photo="{{ asset('storage/' . $rosterEntry->user->profile_photo_path) }}"
                                                         data-name="{{ $rosterEntry->user->name }}"
                                                         data-profile-url="{{ route('users.show', $rosterEntry->user) }}"
                                                         class="cursor-pointer"
                                                         title="Посмотреть фото">
                                                        <x-avatar :photo="$rosterEntry->user->profile_photo_thumb_url"
                                                                  :name="$rosterEntry->user->name"
                                                                  size="sm" />
                                                    </div>
                                                @else
                                                    <x-avatar :name="$rosterEntry->user->name" size="sm" />
                                                @endif
                                            </a>
                                            <div class="min-w-0">
                                                <a href="{{ route('users.show', $rosterEntry->user) }}"
                                                   class="block truncate font-semibold text-brand-800 transition hover:text-accent-600">
                                                    {{ $rosterEntry->user->name }}
                                                </a>
                                                @if ($rosterEntry->user->email_verified_at)
                                                    <span class="inline-flex items-center gap-1 text-xs text-emerald-600">
                                                        @svg('lucide-badge-check', 'h-3.5 w-3.5')Подтвержден
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-2.5">
                                        @if ($rosterEntry->position)
                                            <x-badge>{{ $rosterEntry::POSITIONS[$rosterEntry->position] }}</x-badge>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-2.5">
                                        @if ($rosterEntry->jersey_number)
                                            <x-badge variant="dark">#{{ $rosterEntry->jersey_number }}</x-badge>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-2.5 text-slate-500">{{ $rosterEntry->user->created_at->format('d.m.Y') }}</td>
                                    <td class="px-6 py-2.5">
                                        <x-btn href="{{ route('users.show', $rosterEntry->user) }}" variant="outline" size="sm">
                                            @svg('lucide-eye', 'h-4 w-4')Профиль
                                        </x-btn>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>
        @endif
    </div>
</x-app-layout>