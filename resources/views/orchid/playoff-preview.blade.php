<div class="playoff-preview">
    <h5>Предпросмотр сетки</h5>

    @if(empty($config))
        <div class="alert alert-info">
            Заполните основные настройки для предпросмотра
        </div>
    @else
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <strong>Параметры турнира</strong>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <th>Тип:</th>
                                <td>
                                    @switch($config['format_type'] ?? 'single_elimination')
                                        @case('single_elimination') Олимпийская система @break
                                        @case('double_elimination') Двойная система @break
                                        @case('custom') Пользовательская @break
                                    @endswitch
                                </td>
                            </tr>
                            <tr>
                                <th>Команд:</th>
                                <td>{{ $config['total_teams'] ?? 8 }}</td>
                            </tr>
                            <tr>
                                <th>Тип посева:</th>
                                <td>
                                    @switch($config['seeding_type'] ?? 'standard')
                                        @case('standard') Стандартный @break
                                        @case('group_winners') Победители групп @break
                                        @case('random') Случайный @break
                                        @case('manual') Ручной @break
                                    @endswitch
                                </td>
                            </tr>
                            <tr>
                                <th>Раундов:</th>
                                <td>{{ count($config['rounds_config'] ?? []) ?: ceil(log($config['total_teams'] ?? 8, 2)) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <strong>Распределение команд (1 раунд)</strong>
                    </div>
                    <div class="card-body">
                        <div class="seeding-preview">
                            @php
                                $teamCount = $config['total_teams'] ?? 8;
                                $half = $teamCount / 2;
                                $reverseSeeding = $config['reverse_seeding'] ?? false;
                            @endphp

                            @for($i = 1; $i <= $half; $i++)
                                @php
                                    $homePos = $i;
                                    $awayPos = $teamCount - $i + 1;

                                    // Проверяем специальные выходы
                                    $homeBye = null;
                                    $awayBye = null;
                                    if (!empty($config['special_byes'])) {
                                        foreach($config['special_byes'] as $bye) {
                                            if(($bye['position'] ?? '') == $homePos) $homeBye = $bye;
                                            if(($bye['position'] ?? '') == $awayPos) $awayBye = $bye;
                                        }
                                    }
                                @endphp

                                <div class="match-pair mb-2">
                                    <div class="team-badge {{ $homeBye ? 'bye-team' : '' }}">
                                        <span class="position">{{ $homePos }}</span>
                                        @if($homeBye)
                                            <small class="bye-label">(→ {{ $homeBye['round'] ?? '?' }})</small>
                                        @endif
                                    </div>
                                    <span class="mx-2">vs</span>
                                    <div class="team-badge {{ $awayBye ? 'bye-team' : '' }}">
                                        <span class="position">{{ $awayPos }}</span>
                                        @if($awayBye)
                                            <small class="bye-label">(→ {{ $awayBye['round'] ?? '?' }})</small>
                                        @endif
                                    </div>
                                </div>
                            @endfor

                            @if($reverseSeeding)
                                <div class="alert alert-info mt-2">
                                    <small>✓ Обратный посев включен</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(!empty($config['special_byes']))
            <div class="card mt-3">
                <div class="card-header">
                    <strong>Специальные выходы (BYE)</strong>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                        <tr>
                            <th>Позиция</th>
                            <th>Раунд</th>
                            <th>Описание</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($config['special_byes'] as $bye)
                            <tr>
                                <td>{{ $bye['position'] ?? '-' }}</td>
                                <td>{{ $bye['round'] ?? '-' }}</td>
                                <td>{{ $bye['description'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if(!empty($config['rounds_config']))
            <div class="card mt-3">
                <div class="card-header">
                    <strong>Конфигурация раундов</strong>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                        <tr>
                            <th>Раунд</th>
                            <th>Команд</th>
                            <th>Матчей</th>
                            <th>До побед</th>
                            <th>Тай-брейк</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($config['rounds_config'] as $roundNum => $round)
                            <tr>
                                <td>{{ $round['name'] ?? "Раунд $roundNum" }}</td>
                                <td>{{ $round['teams'] ?? '-' }}</td>
                                <td>{{ $round['matches'] ?? '-' }}</td>
                                <td>{{ $round['best_of'] ?? 1 }}</td>
                                <td>
                                    @switch($round['tie_breaker'] ?? 'golden_set')
                                        @case('golden_set') Золотой сет @break
                                        @case('extra_game') Доп. игра @break
                                        @case('points') По очкам @break
                                    @endswitch
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif
</div>

<style>
    .playoff-preview {
        padding: 15px;
    }
    .match-pair {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 8px;
        background: #f8f9fa;
        border-radius: 5px;
    }
    .team-badge {
        display: flex;
        align-items: center;
        gap: 5px;
        padding: 3px 10px;
        background: white;
        border-radius: 20px;
        border: 1px solid #dee2e6;
    }
    .team-badge.bye-team {
        background: #fff3cd;
        border-color: #ffc107;
    }
    .position {
        font-weight: bold;
        color: #007bff;
    }
    .bye-label {
        color: #856404;
        font-size: 0.8rem;
    }
</style>
