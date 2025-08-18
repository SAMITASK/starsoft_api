<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Models\Product\TypeModel;
use Illuminate\Http\Request;

class TypeController extends Controller
{
    public function show(Request $request)
    {
        $conexion = 'sqlsrv_' . $request->input('company');
        
        $types = TypeModel::on($conexion)
            ->get()
            ->map(function ($t) {
                return [
                    'id'   => $t->COD_TIPO,   // este será el valor real
                    'name' => $t->DES_TIPO, // este será lo que se muestra
                ];
            });

        return response()->json($types);
    }
}
