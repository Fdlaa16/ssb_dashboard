<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Standing extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'club_id',
        'total',
        'win',
        'draw',
        'lose',
        'goals_in',
        'goals_conceded',
        'goal_difference',
        'points',
    ];

    public function club()
    {
        return $this->belongsTo(Club::class, 'club_id', 'id');
    }

    public function scheduleMatch()
    {
        return $this->hasMany(ScheduleMatch::class, 'schedule_match_id', 'id');
    }

    public function scheduleTraining()
    {
        return $this->hasMany(ScheduleTraining::class, 'schedule_training_id', 'id');
    }
}
