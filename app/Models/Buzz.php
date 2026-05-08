<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Buzz extends Model
{
    // Beritahu Laravel nama tabel yang benar
    protected $table = 'buzzes';

    protected $fillable = ['room_id', 'player_id', 'reaction_time_ms'];
}
