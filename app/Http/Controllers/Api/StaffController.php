<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Responsible;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function filterStaff(Request $request)
    {
        $conexion = 'sqlsrv_' . $request->input('company');

        $staff = Responsible::on($conexion)
                    ->get()
                    ->map(function ($f) {
                        return [
                            'id'   => $f->RESPONSABLE_CODIGO,
                            'name' => $f->RESPONSABLE_NOMBRE,
                        ];
                    });

        return response()->json($staff);
    }
}
