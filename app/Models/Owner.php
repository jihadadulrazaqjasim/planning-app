<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Owner extends Model
{
    use HasFactory;
    
    public function board()
    {
        return $this->hasMany(Board::class);
    }

    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
