<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LolProfile extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'riot_pseudo', 'riot_tag'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

