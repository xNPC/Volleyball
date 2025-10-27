<div>
    <!-- Хлебные крошки -->
    <div class="mb-4">
        <button
            wire:click="$emit('backToGroups')"
            class="text-indigo-600 hover:text-indigo-900 flex items-center"
        >
            ← Назад к группам
        </button>
        <h2 class="text-2xl font-bold text-gray-900 mt-2">Группа: {{ $group->name }}</h2>
    </div>

    <!-- Таблица команд -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Команда
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Очки
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Позиция
                </th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
            @foreach($teams as $index => $team)
                <tr class="@if($index < 2) bg-green-50 @elseif($index >= count($teams) - 2) bg-red-50 @else hover:bg-gray-50 @endif">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $team->name }}
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $team->points }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $index + 1 }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
