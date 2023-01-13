<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lable extends Model
{
    use HasFactory;
    protected $fillabel = [
        'title','task_id',
     ];

     
     public function task()
     {
         return $this->belongsTo(Task::class);
     }
}
