<x-app-layout>
    <div>
        <x-breadcrumb :items="[
            ['label' => 'Команды', 'url' => route('teams.index')],
            ['label' => $team->name],
        ]" />

        <div class="grid gap-6 lg:grid-cols-3">
            <div>
                <x-card class="p-6 text-center">
                    <div class="mx-auto mb-4 w-fit">
                        <x-team-logo :name="$team->name" size="xl" />
                    </div>

                    <h1 class="font-display text-xl font-bold text-brand-800">{{ $team->name }}</h1>

                    @if ($team->city)
                        <p class="mt-1 flex items-center justify-center gap-1 text-slate-500">
                            @svg('lucide-map-pin', 'h-4 w-4'){{ $team->city }}
                        </p>
                    @endif

                    @if ($team->description)
                        <p class="mt-3 text-sm leading-relaxed text-slate-500">{{ $team->description }}</p>
                    @endif

                    <div class="mt-5 grid grid-cols-3 gap-2 border-y border-slate-100 py-4">
                        <x-stat label="Турниров">{{ $team->activeTournaments->count() }}</x-stat>
                        <x-stat label="Заявок">{{ $team->tournamentApplications->count() }}</x-stat>
                        <x-stat label="Игроков">?</x-stat>
                    </div>

                    @if ($team->captain)
                        <div class="mt-5 text-left">
                            <h2 class="mb-3 flex items-center gap-2 font-display font-bold text-brand-800">
                                @svg('lucide-crown', 'h-4 w-4 text-accent-500')Капитан
                            </h2>
                            <div class="flex items-center gap-3">
                                @if ($team->captain->profile_photo_path)
                                    <div data-photo="{{ asset('storage/' . $team->captain->profile_photo_path) }}"
                                         data-name="{{ $team->captain->name }}"
                                         data-profile-url="{{ route('users.show', $team->captain) }}"
                                         class="cursor-pointer"
                                         title="Посмотреть фото">
                                        <x-avatar :photo="$team->captain->profile_photo_thumb_url" :name="$team->captain->name" size="md" />
                                    </div>
                                @else
                                    <x-avatar :name="$team->captain->name" size="md" />
                                @endif
                                <div class="min-w-0">
                                    <a href="{{ route('users.show', $team->captain) }}"
                                       class="block truncate font-semibold text-brand-800 transition hover:text-accent-600">
                                        {{ $team->captain->name }}
                                    </a>
                                    <p class="truncate text-xs text-slate-400">{{ $team->captain->email }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <p class="mt-5 border-t border-slate-100 pt-4 text-xs text-slate-400">
                        @svg('lucide-calendar-days', 'inline h-3.5 w-3.5') Создана: {{ $team->created_at->format('d.m.Y') }}
                    </p>
                </x-card>
            </div>

            <div class="space-y-6 lg:col-span-2">
                @if ($team->activeTournaments->count() > 0)
                    <div>
                        <h2 class="mb-4 flex items-center gap-2 font-display text-lg font-bold text-brand-800">
                            @svg('lucide-trophy', 'h-5 w-5 text-accent-500')Активные турниры
                        </h2>
                        <div class="grid gap-4 md:grid-cols-2">
                            @foreach ($team->activeTournaments as $tournament)
                                <x-card class="flex flex-col p-5 transition hover:bg-slate-50/70">
                                    <a href="{{ route('tournaments.show', $tournament) }}"
                                       class="font-display font-bold text-brand-800 transition hover:text-accent-600">
                                        {{ $tournament->name }}
                                    </a>
                                    <p class="mt-1 text-sm text-slate-500">
                                        @svg('lucide-calendar-days', 'inline h-4 w-4'){{ $tournament->start_date->format('d.m.Y') }} — @if ($tournament->end_date){{ $tournament->end_date->format('d.m.Y') }}@endif
                                    </p>
                                    <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">
                                        <x-badge variant="green">@svg('lucide-check-circle', 'h-3.5 w-3.5')Участвует</x-badge>
                                        <a href="{{ route('tournaments.teams.roster', ['tournament' => $tournament, 'team' => $team]) }}"
                                           class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-brand-700 transition hover:bg-slate-50 hover:text-brand-800">
                                            @svg('lucide-users', 'h-4 w-4')Состав
                                        </a>
                                    </div>
                                </x-card>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($team->tournamentApplications->count() > 0)
                    <x-card class="overflow-hidden">
                        <div class="px-6 pt-6">
                            <h3 class="flex items-center gap-2 font-display text-lg font-bold text-brand-800">
                                @svg('lucide-activity', 'h-5 w-5 text-accent-500')История заявок
                            </h3>
                        </div>
                        <div class="mt-4 overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="border-y border-slate-100 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                                        <th class="px-6 py-3 font-semibold">Турнир</th>
                                        <th class="px-6 py-3 font-semibold">Статус</th>
                                        <th class="px-6 py-3 font-semibold">Дата подачи</th>
                                        <th class="px-6 py-3 font-semibold">Игроков в заявке</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($team->tournamentApplications as $application)
                                        @php
                                            $statusBadge = match ($application->status) {
                                                'approved' => 'green',
                                                'rejected' => 'red',
                                                'pending' => 'amber',
                                                default => 'slate',
                                            };
                                            $statusLabel = match ($application->status) {
                                                'approved' => 'Принята',
                                                'rejected' => 'Отклонена',
                                                'pending' => 'На рассмотрении',
                                                default => $application->status,
                                            };
                                        @endphp
                                        <tr class="transition hover:bg-slate-50">
                                            <td class="px-6 py-2.5">
                                                <a href="{{ route('tournaments.show', $application->tournament) }}"
                                                   class="font-medium text-brand-800 transition hover:text-accent-600">
                                                    {{ $application->tournament->name ?? 'Неизвестный турнир' }}
                                                </a>
                                            </td>
                                            <td class="px-6 py-2.5"><x-badge :variant="$statusBadge">{{ $statusLabel }}</x-badge></td>
                                            <td class="px-6 py-2.5 text-slate-500">{{ $application->created_at->format('d.m.Y H:i') }}</td>
                                            <td class="px-6 py-2.5">
                                                @if ($application->tournament)
                                                    <a href="{{ route('tournaments.teams.roster', ['tournament' => $application->tournament, 'team' => $team]) }}"
                                                       class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 px-2.5 py-1 text-xs font-medium text-brand-700 transition hover:bg-slate-50">
                                                        @svg('lucide-users', 'h-3.5 w-3.5')Состав
                                                        <span class="rounded-full bg-brand-100 px-1.5 text-brand-800">{{ $application->roster->count() }}</span>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </x-card>
                @else
                    <x-card class="py-14 text-center">
                        <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                            @svg('lucide-list', 'h-7 w-7')
                        </div>
                        <h4 class="font-display text-lg font-bold text-brand-800">Нет заявок на турниры</h4>
                        <p class="mt-1 text-slate-500">Команда пока не подавала заявки на участие в турнирах</p>
                    </x-card>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>