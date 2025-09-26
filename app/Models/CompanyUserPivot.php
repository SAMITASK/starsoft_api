<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyUserPivot extends Model
{
    protected $connection = 'auth_db';
    protected $table = 'company_user';

    protected $fillable = [
        'user_id',
        'company_id',
        'user_code',
    ];
}
