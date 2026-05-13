<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderApprovalAreaMap extends Model
{
    protected $connection = 'bdwenco';
    protected $table = 'vw_ListadoAprobacionesAreaMap';
    public $timestamps = false;
    public $incrementing = false;
    protected $guarded = [];
}
