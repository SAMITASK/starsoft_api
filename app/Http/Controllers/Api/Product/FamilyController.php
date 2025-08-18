<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Models\Product\FamilyModel;
use Illuminate\Http\Request;

class FamilyController extends Controller
{
    public function show(Request $request)
    {
        $conexion = 'sqlsrv_' . $request->input('company');

        $families = FamilyModel::on($conexion)
            ->get()
            ->map(function ($f) {
                return [
                    'id'   => $f->FAM_CODIGO,   // valor real
                    'name' => $f->FAM_NOMBRE,   // lo que se muestra
                ];
            });

        return response()->json($families);
    }
}
