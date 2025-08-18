<?php

namespace App\Models;

use App\Models\Product\FamilyModel;
use App\Models\Product\TypeModel;
use App\Traits\HasDynamicConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductModel extends Model
{
    use HasFactory, HasDynamicConnection;

    protected $table = 'MAEART';
    protected $primaryKey = 'ACODIGO';
    public $incrementing = false; // varchar
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'ACODIGO',   // Código
        'ADESCRI',   // Descripción
        'AUNIDAD',   // Unidad
        'AFSERIE',   // Serie
        'AFLOTE',    // Lote
        'AFAMILIA',  // Familia
        'AMODELO',   // Línea / Modelo
        'AGRUPO',    // Grupo
        'ATIPO',     // Tipo
        'AUSER',     // Usuario
        'AFECHA',    // Fecha de registro
    ];

    public function type()
    {
        return $this->belongsTo(TypeModel::class, 'ATIPO', 'COD_TIPO');
    }

    public function family()
    {
        return $this->belongsTo(FamilyModel::class, 'AFAMILIA', 'FAM_CODIGO');
    }

    public function oc()
    {
        return $this->hasMany(OCDModel::class, 'OC_CCODIGO', 'ACODIGO');
    }

    public function os()
    {
        return $this->hasMany(OCSDModel::class, 'OC_CODSERVICIO', 'ACODIGO');
    }
}
