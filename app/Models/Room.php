<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = ['code', 'status', 'timer_ends_at'];

    // Tambahkan relasi ini ke tabel Player:
    public function players()
    {
        return $this->hasMany(Player::class);
    }
}
