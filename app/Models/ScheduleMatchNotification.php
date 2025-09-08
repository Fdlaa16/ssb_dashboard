<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleMatchNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_match_id',
        'type',
        'is_sent',
        'scheduled_at',
        'sent_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'is_sent' => 'boolean',
    ];

    public function scheduleMatch()
    {
        return $this->belongsTo(ScheduleMatch::class);
    }
}
