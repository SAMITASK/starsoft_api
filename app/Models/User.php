<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $connection = 'auth_db';
    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'cargo',
        'status',
        'company_ids'
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function companiesPivot()
    {
        return $this->hasMany(CompanyUserPivot::class, 'user_id');
    }
    
}
