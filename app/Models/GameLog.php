<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameLog extends Model
{
    use HasFactory;

    protected $fillable = ['room_id', 'player_id', 'action', 'payload'];

    // Otomatis ubah JSON di database menjadi Array di PHP
    protected $casts = [
        'payload' => 'array',
    ];

    public function player()
    {
        return $this->belongsTo(Player::class);
    }
}
