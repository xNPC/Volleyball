<x-app-layout>
    <div>
        <x-breadcrumb :items="[
            ['label' => 'Турниры', 'url' => route('tournaments.index')],
            ['label' => $group->stage->tournament->name, 'url' => route('tournaments.show', $group->stage->tournament)],
            ['label' => $group->stage->name, 'url' => route('stages.show', ['tournament' => $group->stage->tournament, 'stage' => $group->stage])],
            ['label' => $group->name],
        ]" />

        <x-card class="mb-6 p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="font-display text-2xl font-bold tracking-tight text-brand-800 sm:text-3xl">
                        @svg('lucide-users', 'inline h-7 w-7 text-accent-500'){{ $group->name }}
                    </h1>
                    <p class="mt-1 text-slate-500">{{ $group->stage->name }} • {{ $group->stage->tournament->name }}</p>
                </div>
                <div class="shrink-0">
                    <x-badge variant="accent">{{ $group->teams->count() }} команд</x-badge>
                </div>
            </div>
        </x-card>

        <x-card class="overflow-hidden">
            @if ($group->standings->isNotEmpty())
                <div class="px-6 pt-6">
                    <h3 class="flex items-center gap-2 font-display text-lg font-bold text-brand-800">
                        @svg('lucide-list', 'h-5 w-5 text-accent-500')Турнирная таблица
                    </h3>
                </div>
            @endif
            @include('partials.group-table', ['group' => $group])
            @if ($group->standings->isNotEmpty())
                <div class="mt-4 flex flex-wrap gap-4 border-t border-slate-100 px-6 py-4 text-sm">
                    <span class="flex items-center gap-1.5">
                        <span class="h-3 w-3 rounded-full bg-emerald-500"></span> Выход в плей-офф
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="h-3 w-3 rounded-full bg-red-500"></span> Выбывание
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="h-3 w-3 rounded-full bg-slate-300"></span> Нейтрально
                    </span>
                </div>
            @endif
        </x-card>
    </div>
</x-app-layout>