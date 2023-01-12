<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'type',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    
    // public function owner()
    // {
    //     return $this->hasOne(Owner::class);
    // }

    // public function developer()
    // {
    //     return $this->hasOne(Developer::class);
    // }

    // public function tester()
    // {
    //     return $this->hasOne(Tester::class);
    // }

    
    public function task()
    {
        return $this->hasMany(Task::class);
    }

    
    public function board()
    {
        return $this->hasMany(Board::class);
    }
}
