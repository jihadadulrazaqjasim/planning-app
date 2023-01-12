<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

    protected $fillabel = [
        'user_name',
        'action',
        'detail',
    ];

    
    public function board()
    {
        return $this->belongsTo(Board::class);
    }
    
}
