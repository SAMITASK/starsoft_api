<?php

namespace App\Models;

use App\Traits\HasDynamicConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OCDModel extends Model
{
    use HasFactory, HasDynamicConnection;

    protected $table = 'COMOVD';
    public $timestamps = false;
}
