<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    protected $connection = 'bdwenco';
    protected $table = 'ListadoAprobaciones';
    protected $primaryKey = 'idAprobaciones';
    public $timestamps = false;

    protected $fillable = [
        'codigoEmpresa',
        'nombreEmpresa',
        'modulo',
        'tipo',
        'identificador',
        'asunto',
        'usuarioGeneracion',
        'fechaGeneracion',
        'estado',
        'leido',
        'usuarioAprobacion',
        'fechaAprobacion',
        'estadoEliminado',
        'correoRespuesta'
    ];
}
