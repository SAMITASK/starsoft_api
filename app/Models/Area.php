<?php

namespace App\Models;

use App\Traits\HasDynamicConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory, HasDynamicConnection;
    
    protected $table = 'AREA';
    protected $primaryKey = 'AREA_CODIGO';
    protected $keyType = 'string';
    public $timestamps = false;

    protected $appends = ['id', 'name'];

    protected $hidden = ['AREA_CODIGO', 'AREA_DESCRIPCION'];

    public function getIdAttribute()
    {
        return $this->attributes['AREA_CODIGO'];
    }

    public function getNameAttribute()
    {
        return $this->attributes['AREA_DESCRIPCION'];
    }
}
