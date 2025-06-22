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
        'score',
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
}
