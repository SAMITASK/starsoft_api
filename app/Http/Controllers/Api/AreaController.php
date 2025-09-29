<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Area;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    public function filterCompany(Request $request)
    {
        $conexion = 'sqlsrv_' . $request->input('company');

        $areas = Area::on($conexion)
            ->select('AREA_CODIGO', 'AREA_DESCRIPCION')
            ->orderBy('AREA_DESCRIPCION', 'ASC')
            ->get();

        return response()->json($areas);
    }

    

}
