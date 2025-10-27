<div>
    <!-- Хлебные крошки -->
    <div class="mb-4">
        <button
            wire:click="backToStages"
            class="text-indigo-600 hover:text-indigo-900 flex items-center"
        >
            ← Назад к этапам
        </button>
        <h2 class="text-2xl font-bold text-gray-900 mt-2">{{ $stage->name }}</h2>
    </div>

    @if(!$selectedGroup)
        <!-- Список групп -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($groups as $group)
                <div
                    wire:click="selectGroup({{ $group->id }})"
                    class="bg-white overflow-hidden shadow rounded-lg cursor-pointer hover:shadow-lg transition-shadow duration-300"
                >
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900">{{ $group->name }}</h3>
                                <p class="text-sm text-gray-500">{{ $group->teams_count }} команд</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- Команды группы -->
        <livewire:group-teams :groupId="$selectedGroup" :key="'group-'.$selectedGroup" />
    @endif
</div>
