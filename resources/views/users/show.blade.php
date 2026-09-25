<x-app-layout>
    <div>
        <x-breadcrumb :items="[
            ['label' => 'Игроки', 'url' => route('users.index')],
            ['label' => $user->name],
        ]" />

        <div class="grid gap-6 lg:grid-cols-3">
            <div>
                <x-card class="p-6 text-center">
                    @if ($user->profile_photo_path)
                        <div data-photo="{{ asset('storage/' . $user->profile_photo_path) }}"
                             data-name="{{ $user->name }}"
                             data-profile-url="{{ route('users.show', $user) }}"
                             class="mx-auto mb-4 w-fit cursor-pointer"
                             title="Посмотреть фото">
                            <x-avatar :photo="$user->profile_photo_thumb_url" :name="$user->name" size="xl" />
                        </div>
                    @else
                        <div class="mx-auto mb-4 w-fit">
                            <x-avatar :name="$user->name" size="xl" />
                        </div>
                    @endif

                    <h1 class="font-display text-xl font-bold text-brand-800">{{ $user->name }}</h1>

                    <p class="mt-1 text-sm">
                        @if ($user->email_verified_at)
                            <span class="inline-flex items-center gap-1 text-emerald-600">
                                @svg('lucide-badge-check', 'h-4 w-4')Подтвержденный аккаунт
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-amber-600">
                                @svg('lucide-clock', 'h-4 w-4')Ожидает подтверждения
                            </span>
                        @endif
                    </p>

                    <div class="mt-5 space-y-2.5 rounded-xl bg-slate-50 p-4 text-left text-sm text-slate-600">
                        @if ($user->birthday)
                            <p class="flex items-center gap-2.5">
                                @svg('lucide-cake-slice', 'h-4 w-4 text-slate-400')Дата рождения: {{ $user->birthday->format('d.m.Y') }}
                            </p>
                        @endif
                        <p class="flex items-center gap-2.5 break-all">
                            @svg('lucide-mail', 'h-4 w-4 shrink-0 text-slate-400'){{ $user->email }}
                        </p>
                        <p class="flex items-center gap-2.5">
                            @svg('lucide-calendar-days', 'h-4 w-4 text-slate-400')Зарегистрирован: {{ $user->created_at->format('d.m.Y') }}
                        </p>
                        <p class="flex items-center gap-2.5">
                            @svg('lucide-clock', 'h-4 w-4 text-slate-400')Последняя активность: {{ $user->updated_at->diffForHumans() }}
                        </p>
                    </div>
                </x-card>
            </div>

            <div class="lg:col-span-2">
                <x-card class="p-6">
                    <h2 class="mb-5 flex items-center gap-2 font-display text-lg font-bold text-brand-800">
                        @svg('lucide-list', 'h-5 w-5 text-accent-500')Заявлен в командах
                    </h2>

                    @if ($user->tournamentApplications->count() > 0)
                        <div class="grid gap-4 md:grid-cols-2">
                            @foreach ($user->tournamentApplications as $application)
                                @php
                                    $team = $application->team;
                                    $tournament = $application->tournament;
                                    $userRoster = $user->applicationRosters
                                        ->where('application_id', $application->id)
                                        ->first();
                                    $statusBadge = match ($application->status) {
                                        'approved' => 'green',
                                        'rejected' => 'red',
                                        default => 'amber',
                                    };
                                    $statusLabel = match ($application->status) {
                                        'approved' => 'Принята',
                                        'rejected' => 'Отклонена',
                                        default => 'На рассмотрении',
                                    };
                                @endphp
                                <x-card class="p-5 transition hover:bg-slate-50/70">
                                    <div class="flex items-center gap-3">
                                        <x-team-logo :name="$team->name ?? '?'" size="sm" />
                                        <div class="min-w-0">
                                            @if ($team)
                                                <a href="{{ route('teams.show', $team) }}"
                                                   class="block truncate font-display font-bold text-brand-800 transition hover:text-accent-600">
                                                    {{ $team->name }}
                                                </a>
                                            @endif
                                            @if ($tournament)
                                                <a href="{{ route('tournaments.show', $tournament) }}"
                                                   class="block truncate text-sm text-slate-500 transition hover:text-accent-600">
                                                    @svg('lucide-trophy', 'inline h-3.5 w-3.5'){{ $tournament->name }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-3">
                                        <x-badge :variant="$statusBadge">{{ $statusLabel }}</x-badge>
                                        <span class="text-xs text-slate-400">{{ $application->created_at->format('d.m.Y') }}</span>
                                        @if ($userRoster && $userRoster->position)
                                            <x-badge>{{ $userRoster::POSITIONS[$userRoster->position] }}</x-badge>
                                        @endif
                                    </div>
                                </x-card>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center py-10 text-center">
                            <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                                @svg('lucide-list', 'h-7 w-7')
                            </div>
                            <p class="text-slate-500">Игрок пока не участвовал в заявках на турниры</p>
                        </div>
                    @endif
                </x-card>

                @if ($user->applicationRosters->count() > 0)
                    <x-card class="mt-6 overflow-hidden">
                        <div class="px-6 pt-6">
                            <h3 class="flex items-center gap-2 font-display text-lg font-bold text-brand-800">
                                @svg('lucide-activity', 'h-5 w-5 text-accent-500')История участий в турнирах
                            </h3>
                        </div>
                        <div class="mt-4 overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="border-y border-slate-100 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                                        <th class="px-6 py-3 font-semibold">Команда</th>
                                        <th class="px-6 py-3 font-semibold">Турнир</th>
                                        <th class="px-6 py-3 font-semibold">Позиция</th>
                                        <th class="px-6 py-3 font-semibold">Дата</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($user->applicationRosters as $roster)
                                        @php
                                            $application = $roster->Application;
                                            $team = $application->team ?? null;
                                            $tournament = $application->tournament ?? null;
                                        @endphp
                                        <tr class="transition hover:bg-slate-50">
                                            <td class="px-6 py-2.5">
                                                @if ($team)
                                                    <a href="{{ route('teams.show', $team) }}" class="font-medium text-brand-800 hover:text-accent-600">{{ $team->name }}</a>
                                                @else
                                                    <span class="text-slate-400">Неизвестная команда</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-2.5">
                                                @if ($tournament)
                                                    <a href="{{ route('tournaments.show', $tournament) }}" class="text-slate-600 hover:text-accent-600">{{ $tournament->name }}</a>
                                                @else
                                                    <span class="text-slate-400">-</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-2.5">
                                                @if ($roster->position)
                                                    <x-badge>{{ $roster::POSITIONS[$roster->position] }}</x-badge>
                                                @else
                                                    <span class="text-slate-400">-</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-2.5 text-slate-500">{{ $roster->created_at->format('d.m.Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </x-card>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>