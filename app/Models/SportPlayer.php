<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SportPlayer extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'player_id',
        'sport_id',
    ];

    public function player()
    {
        return $this->belongsTo(Player::class, 'player_id', 'id');
    }

    public function sport()
    {
        return $this->belongsTo(Sport::class, 'sport_id', 'id');
    }
}
