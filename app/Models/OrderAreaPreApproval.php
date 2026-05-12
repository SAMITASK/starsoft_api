<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderAreaPreApproval extends Model
{
    protected $connection = 'auth_db';

    protected $table = 'order_area_pre_approvals';

    protected $fillable = [
        'company_code',
        'order_type',
        'order_code',
        'area_manager_user_id',
        'area_manager_name',
        'area_manager_approved_at',
    ];

    protected $casts = [
        'area_manager_approved_at' => 'datetime',
    ];
}
