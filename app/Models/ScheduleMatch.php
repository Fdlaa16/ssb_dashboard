<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScheduleMatch extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'first_club_id',
        'secound_club_id',
        'stadium_id',
        'schedule_date',
        'schedule_start_at',
        'schedule_end_at',
        'first_club_score',
        'secound_club_score',
        'status'
    ];

    public function stadium()
    {
        return $this->belongsTo(Stadium::class, 'stadium_id', 'id');
    }

    public function firstClub()
    {
        return $this->belongsTo(Club::class, 'first_club_id', 'id')->withTrashed();
    }

    public function secoundClub()
    {
        return $this->belongsTo(Club::class, 'secound_club_id', 'id')->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function scheduleMatchNotifications()
    {
        return $this->hasMany(ScheduleMatchNotification::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($scheduleMatch) {
            $scheduleMatch->createNotifications();
        });

        static::updated(function ($scheduleMatch) {
            if ($scheduleMatch->wasChanged(['schedule_date', 'schedule_start_at'])) {
                $scheduleMatch->updateNotifications();
            }
        });
    }

    public function createNotifications()
    {
        $scheduleDateTime = \Carbon\Carbon::parse($this->schedule_date . ' ' . $this->schedule_start_at);

        $notifications = [
            [
                'type' => 'h_minus_7',
                'scheduled_at' => $scheduleDateTime->copy()->subDays(7)->startOfDay(),
            ],
            [
                'type' => 'h_minus_1',
                'scheduled_at' => $scheduleDateTime->copy()->subDay()->startOfDay(),
            ],
            [
                'type' => 'day_h',
                'scheduled_at' => $scheduleDateTime->copy()->startOfDay(),
            ],
        ];

        foreach ($notifications as $notification) {
            ScheduleMatchNotification::create(array_merge($notification, [
                'schedule_match_id' => $this->id,
            ]));
        }
    }

    public function updateNotifications()
    {
        $this->scheduleMatchNotifications()->where('is_sent', false)->delete();
        $this->createNotifications();
    }
}
