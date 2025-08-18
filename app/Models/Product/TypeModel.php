<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasDynamicConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TypeModel extends Model
{
    use HasFactory, HasDynamicConnection;
    
    protected $table = 'TIPO_ARTICULO';
    protected $primaryKey = 'COD_TIPO';
    public $incrementing = false; // porque es varchar
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'COD_TIPO',   // Código de tipo (1, 2, 3, etc. pero en varchar)
        'DES_TIPO',   // Descripción (MERCADERIAS, SERVICIOS, etc.)
        'FLG_VENTA',  // Bandera booleana
    ];
}
