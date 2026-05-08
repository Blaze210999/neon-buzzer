<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    protected $fillable = ['room_id', 'name', 'score', 'session_id'];

    // Tambahkan relasi ini:
    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
