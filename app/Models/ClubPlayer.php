<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClubPlayer extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        // 'club_id',
        'player_id',
        'back_number',
        'position',
        'is_captain',
        'status',
        'category'
    ];

    // public function club()
    // {
    //     return $this->belongsTo(Club::class, 'club_id', 'id');
    // }

    public function player()
    {
        return $this->belongsTo(Player::class, 'player_id', 'id');
    }
}
