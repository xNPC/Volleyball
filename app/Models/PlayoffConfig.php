<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayoffConfig extends Model
{
    protected $fillable = [
        'stage_id',
        'format_type', // 'single_elimination', 'double_elimination', 'round_robin', 'custom'
        'total_teams',
        'bracket_structure', // Полная структура сетки
        'rounds_config', // Конфигурация каждого раунда
        'tie_breakers', // Правила определения ничьей
        'advancement_rules', // Правила выхода в следующий раунд
        'metadata', // Дополнительные данные
    ];

    protected $casts = [
        'bracket_structure' => 'array',
        'rounds_config' => 'array',
        'tie_breakers' => 'array',
        'advancement_rules' => 'array',
        'metadata' => 'array',
    ];

    public function stage()
    {
        return $this->belongsTo(TournamentStage::class);
    }
}
