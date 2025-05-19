<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Club extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'code',
        'name',
        'status',
        'description',
    ];

    public static $code_prefix = "CLB";

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            try {
                $model->code = self::getNextCode();
            } catch (\Exception $e) {
                abort(500, $e->getMessage());
            }
        });
    }

    public static function getNextCode()
    {
        $last_number = self::withTrashed()->max('code');
        $next_number = empty($last_number) ? 1 : ((int) explode('-', $last_number)[1] + 1);

        return self::makeCode($next_number);
    }

    public static function makeCode($next_number)
    {
        return (string) self::$code_prefix . '-' . str_pad($next_number, 5, 0, STR_PAD_LEFT);
    }

    public function clubPlayer()
    {
        return $this->belongsTo(ClubPlayer::class, 'club_id', 'id');
    }

    public function players()
    {
        return $this->belongsToMany(Player::class, 'club_players', 'club_id', 'player_id');
    }

    public function scheduleMatch()
    {
        return $this->hasMany(ScheduleMatch::class);
    }

    public function scheduleTraining()
    {
        return $this->hasMany(ScheduleTraining::class);
    }

    public function stadium()
    {
        return $this->hasMany(Stadium::class, 'club_id', 'id');
    }

    public function standing()
    {
        return $this->belongsTo(Standing::class, 'club_id', 'id');
    }
}
