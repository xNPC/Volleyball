<div>
    <!-- Хлебные крошки -->
    <div class="mb-4">
        <button
            wire:click="$emit('backToList')"
            class="text-indigo-600 hover:text-indigo-900 flex items-center"
        >
            ← Назад к турнирам
        </button>
        <h2 class="text-2xl font-bold text-gray-900 mt-2">{{ $tournament->name }}</h2>
    </div>

    @if(!$selectedStage)
        <!-- Список этапов -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Этапы турнира</h3>
            </div>
            <ul class="divide-y divide-gray-200">
                @foreach($stages as $stage)
                    <li>
                        <button
                            wire:click="selectStage({{ $stage->id }})"
                            class="block hover:bg-gray-50 w-full text-left"
                        >
                            <div class="px-4 py-4 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <p class="text-sm font-medium text-indigo-600 truncate">
                                            {{ $stage->name }}
                                        </p>
                                    </div>
                                    <div class="ml-2 flex-shrink-0 flex">
                                        <p class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            Этап {{ $stage->order }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>
    @else
        <!-- Группы этапа -->
        <livewire:stage-groups :stageId="$selectedStage" :key="'stage-'.$selectedStage" />
    @endif
</div>
