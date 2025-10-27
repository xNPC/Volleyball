<div>
    @if(!$selectedTournament)
        <!-- Список турниров - адаптируйте под ваш дизайн -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Доступные турниры</h3>
            </div>
            <div class="divide-y divide-gray-200">
                @foreach($tournaments as $tournament)
                    <div class="p-6 hover:bg-gray-50 transition duration-150 ease-in-out cursor-pointer"
                         wire:click="selectTournament({{ $tournament->id }})">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0 bg-indigo-100 rounded-md p-3">
                                    <!-- Ваша иконка -->
                                </div>
                                <div>
                                    <h4 class="text-lg font-medium text-gray-900">{{ $tournament->name }}</h4>
                                    <p class="text-gray-600 text-sm mt-1">{{ $tournament->description }}</p>
                                    <div class="flex items-center space-x-4 mt-2 text-sm text-gray-500">
                                        <span>{{ $tournament->start_date->format('d.m.Y') }} - {{ $tournament->end_date->format('d.m.Y') }}</span>
                                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">
                                            {{ $tournament->stages_count }} этапов
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <!-- Детали турнира -->
        <livewire:tournament-stages :tournamentId="$selectedTournament" :key="'tournament-'.$selectedTournament" />
    @endif
</div>
