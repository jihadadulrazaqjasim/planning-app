<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    use HasFactory;

    protected $fillable = [
        'title','description','user_id'
    ];

    
    // public function owner()
    // {
    //     return $this->belongsTo(Owner::class);
    // }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function status()
    {
        return $this->hasMany(Status::class);
    }
   
    public function task()
    {
        return $this->hasMany(Task::class);
    }
}
