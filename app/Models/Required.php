<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Required extends Model
{
    protected $table = 'TABAYU';
    protected $primaryKey = 'TCLAVE';
    protected $keyType = 'string';
    public $timestamps = false;
}
