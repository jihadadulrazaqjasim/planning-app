<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_name',
        'action',
        'detail',
        'task_id',
    ];

    
    // public function board()
    // {
    //     return $this->belongsTo(Board::class);
    // }
    
    public function task()
    {
        return $this->belongsTo(Task::class);
    }
    
}
