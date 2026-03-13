<div class="playoff-preview">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Предпросмотр сетки плейофф</h5>
                </div>
                <div class="card-body">
                    @if(empty($config))
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Настройки плейофф для этой группы еще не сохранены.
                            Заполните форму и сохраните настройки для предпросмотра.
                        </div>
                    @else
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <table class="table table-sm table-bordered">
                                    <tr>
                                        <th style="width: 40%">Формат:</th>
                                        <td>
                                            @if($config['format_type'] == 'single_elimination')
                                                Олимпийская система (на вылет)
                                            @else
                                                Двойная система (два шанса)
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Команд в группе:</th>
                                        <td>{{ $group->teams->count() }}</td>
                                    </tr>
                                    <tr>
                                        <th>Тип посева:</th>
                                        <td>
                                            @php
                                                $seedingType = $config['seeding_rules']['type'] ?? 'standard';
                                            @endphp
                                            @switch($seedingType)
                                                @case('standard') Стандартный (1-последний, 2-предпоследний...) @break
                                                @case('groups') По позициям в группах @break
                                                @case('random') Случайный @break
                                                @default Стандартный
                                            @endswitch
                                        </td>
                                    </tr>
                                    @if($config['seeding_rules']['reverse'] ?? false)
                                        <tr>
                                            <th>Обратный посев:</th>
                                            <td><span class="badge bg-info">Включен</span></td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6>Распределение команд в 1 раунде:</h6>
                                        @php
                                            $matchups = $config['matchups'][1] ?? [];
                                            $teamCount = $group->teams->count();
                                        @endphp

                                        @if(empty($matchups))
                                            <p class="text-muted small">Нет данных о распределении</p>
                                        @else
                                            @foreach($matchups as $index => $matchup)
                                                <div class="match-pair mb-2 p-2 border rounded">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="badge bg-primary">Позиция {{ $matchup['home'] ?? $matchup['home_position'] ?? '?' }}</span>
                                                        <span class="mx-3">vs</span>
                                                        <span class="badge bg-secondary">Позиция {{ $matchup['away'] ?? $matchup['away_position'] ?? '?' }}</span>
                                                    </div>
                                                    @if(!empty($matchup['home_bye']) || !empty($matchup['away_bye']))
                                                        <div class="mt-1 small text-center">
                                                            @if(!empty($matchup['home_bye']))
                                                                <span class="badge bg-success me-2">Победитель сразу в раунд {{ $matchup['home_bye'] }}</span>
                                                            @endif
                                                            @if(!empty($matchup['away_bye']))
                                                                <span class="badge bg-success">Победитель сразу в раунд {{ $matchup['away_bye'] }}</span>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <h6>Настройка раундов:</h6>
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                    <tr>
                                        <th>Раунд</th>
                                        <th>До побед</th>
                                        <th>Тай-брейк</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse(($config['rounds_config'] ?? []) as $roundNum => $round)
                                        <tr>
                                            <td>{{ $round['name'] ?? "Раунд {$roundNum}" }}</td>
                                            <td>
                                                @if(($round['best_of'] ?? 1) == 1)
                                                    Один матч
                                                @elseif(($round['best_of'] ?? 1) == 3)
                                                    До 2 побед
                                                @else
                                                    До 3 побед
                                                @endif
                                            </td>
                                            <td>
                                                @switch($round['tie_breaker'] ?? 'golden_set')
                                                    @case('golden_set') Золотой сет @break
                                                    @case('extra_game') Дополнительная игра @break
                                                    @case('points') По очкам @break
                                                    @default Золотой сет
                                                @endswitch
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">
                                                Нет данных о раундах. Будут использованы стандартные настройки.
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        @if(!empty($config['seeding_rules']['special_byes'] ?? []))
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="alert alert-warning">
                                        <h6>Специальные выходы (BYE):</h6>
                                        <ul class="mb-0">
                                            @foreach($config['seeding_rules']['special_byes'] as $bye)
                                                <li>Позиция {{ $bye['position'] }} → сразу в раунд {{ $bye['round'] }}
                                                    @if(!empty($bye['description']))
                                                        <small class="text-muted">({{ $bye['description'] }})</small>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .playoff-preview .match-pair {
        background: #f8f9fa;
        transition: all 0.2s;
    }
    .playoff-preview .match-pair:hover {
        background: #e9ecef;
        transform: translateX(5px);
    }
</style>
