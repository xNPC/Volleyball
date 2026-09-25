<x-app-layout>
    <div>
        <x-breadcrumb :items="[
            ['label' => 'Турниры', 'url' => route('tournaments.index')],
            ['label' => $tournament->name, 'url' => route('tournaments.show', $tournament)],
            ['label' => $stage->name],
        ]" />

        <x-card class="mb-6 p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="font-display text-2xl font-bold tracking-tight text-brand-800 sm:text-3xl">
                        {{ $stage->name }}
                    </h1>
                    <p class="mt-1 text-slate-500">Этап турнира: {{ $tournament->name }}</p>
                </div>
                <div class="shrink-0">
                    <x-badge variant="accent">Этап {{ $stage->order }}</x-badge>
                </div>
            </div>
        </x-card>

        @if ($stage->groups->count() > 0)
            @php $firstGroupId = $groupsWithStandings->first()?->id; @endphp
            <x-card class="overflow-hidden" x-data="{ active: {{ $firstGroupId ?? 0 }}, subview: 'table' }">
                <div class="flex flex-wrap gap-1 border-b border-slate-100 bg-slate-50/70 p-2">
                    @foreach ($groupsWithStandings as $group)
                        <button type="button"
                                @click="active = {{ $group->id }}; subview = 'table'"
                                :class="active === {{ $group->id }} ? 'bg-brand-700 text-white shadow-sm' : 'text-brand-700 hover:bg-slate-200 hover:text-brand-800'"
                                class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-semibold transition">
                            @svg('lucide-users', 'h-4 w-4'){{ $group->name }}
                        </button>
                    @endforeach
                </div>

                <div class="p-6">
                    @foreach ($groupsWithStandings as $group)
                        <div x-show="active === {{ $group->id }}" x-cloak>
                            <div class="mb-4 flex gap-2">
                                <button type="button"
                                        @click="subview = 'table'"
                                        :class="subview === 'table' ? 'bg-brand-100 text-brand-800' : 'text-slate-500 hover:bg-slate-100'"
                                        class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-semibold transition">
                                    @svg('lucide-table', 'h-4 w-4')Турнирная таблица
                                </button>
                                <button type="button"
                                        @click="subview = 'games'"
                                        :class="subview === 'games' ? 'bg-brand-100 text-brand-800' : 'text-slate-500 hover:bg-slate-100'"
                                        class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-semibold transition">
                                    @svg('lucide-volleyball', 'h-4 w-4')Игры группы
                                </button>
                            </div>

                            <div x-show="subview === 'table'" x-cloak>
                                @include('partials.group-table', ['group' => $group])
                            </div>
                            <div x-show="subview === 'games'" x-cloak>
                                @include('partials.group-games', ['group' => $group])
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>
        @else
            <x-card class="p-8 text-center text-slate-500">
                @svg('lucide-info', 'mx-auto mb-2 h-6 w-6 text-slate-400')
                В этом этапе пока нет групп.
            </x-card>
        @endif
    </div>
</x-app-layout>