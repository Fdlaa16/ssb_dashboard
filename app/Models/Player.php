<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Player extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'user_id',
        'code',
        'name',
        'nisn',
        'height',
        'weight',
    ];

    public static $code_prefix = "PLY";

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

    public function clubPlayers()
    {
        return $this->hasMany(ClubPlayer::class, 'player_id', 'id');
    }

    public function clubs()
    {
        return $this->belongsToMany(Club::class, 'club_players', 'player_id', 'club_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function sportPlayers()
    {
        return $this->hasMany(SportPlayer::class, 'player_id', 'id');
    }

    public function sports()
    {
        return $this->belongsToMany(Sport::class, 'sport_players', 'player_id', 'sport_id');
    }

    public function files()
    {
        return $this->morphMany(File::class, 'fileable');
    }

    public function family_card()
    {
        return $this->morphMany(File::class, 'fileable')->where('type', 'family_card')->latest()->one();
    }

    public function report_grades()
    {
        return $this->morphMany(File::class, 'fileable')->where('type', 'report_grades')->latest()->one();
    }

    public function birth_certificate()
    {
        return $this->morphMany(File::class, 'fileable')->where('type', 'birth_certificate')->latest()->one();
    }

    public function avatar()
    {
        return $this->morphMany(File::class, 'fileable')->where('type', 'avatar')->latest()->one();
    }
}
