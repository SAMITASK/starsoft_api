<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Responsible extends Model
{
    protected $table = 'RESPONSABLECMP';
    protected $primaryKey = 'RESPONSABLE_CODIGO';
    protected $keyType = 'string';
    public $timestamps = false;
    

}
