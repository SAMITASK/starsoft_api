<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\CompanyUserPivot;
use App\Models\OCModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    public function reportSuppliers(Request $request)
    {
        try {
            $conexion = 'sqlsrv_' . $request->input('company', '003');
            $dateRange = $request->input('date');
            $type = $request->input('type', 'OC');
            $area = $request->input('area');
            $staff = $request->input('staff');

            if (strpos($dateRange, ' a ') !== false) {
                [$start, $end] = explode(' a ', $dateRange);
                $start = Carbon::parse(trim($start))->format('Y-m-d');
                $end = Carbon::parse(trim($end))->format('Y-m-d');

                if ($start > $end) {
                    [$start, $end] = [$end, $start];
                }
            }

            $responsible = null;

            if (auth()->check()) {
                $user = auth()->user();

                Log::info('Usuario autenticado', ['user' => $user->id, 'cargo' => $user->cargo]);

                if (in_array(strtoupper($user->cargo), ['GERENTE', 'ADMINISTRADOR'])) {
                    $responsible = $staff;
                } else {
                    $userCode = CompanyUserPivot::where('user_id', $user->id)
                        ->where('company_id', $request->input('company', '003'))
                        ->value('user_code');

                    $responsible = $userCode;
                    Log::info('UserCode obtenido', ['userCode' => $userCode]);
                }
            }

            $result = OCModel::getOrdersSummary($conexion, $start, $end, $responsible, $type, $area);
            $areas = Area::getAvailableAreas($conexion, $start, $end, $responsible, $type);

            // Calcular monto máximo
            $maxMonto = $result->max(fn($item) => (float) $item->MONTO_TOTAL);

            return response()->json([
                'data' => $result,
                'maxMonto' => $maxMonto,
                'areas' => $areas,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en reportSuppliers', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Error inesperado al obtener proveedores',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    public function reportAreas(Request $request)
    {
        try {
            $conexion = 'sqlsrv_' . $request->input('company', '003');
            $dateRange = $request->input('date');
            $type = $request->input('type', 'OC');
            $staff = $request->input('staff');

            // Validar rango de fechas
            if (strpos($dateRange, ' a ') !== false) {
                [$start, $end] = explode(' a ', $dateRange);
                $start = Carbon::parse(trim($start))->format('Y-m-d');
                $end = Carbon::parse(trim($end))->format('Y-m-d');

                if ($start > $end) {
                    [$start, $end] = [$end, $start];
                }
            }

            $responsible = null;
            if (auth()->check()) {
                $user = auth()->user();

                if (in_array(strtoupper($user->cargo), ['GERENTE', 'ADMINISTRADOR'])) {
                    $responsible = $staff;
                } else {
                    $userCode = CompanyUserPivot::where('user_id', $user->id)
                        ->where('company_id', $request->input('company', '003'))
                        ->value('user_code');

                    $responsible = $userCode;
                }
            }

            $result = OCModel::reportAreas(
                $conexion,
                $start,
                $end,
                $responsible,
                $type
            );

            $maxMonto = $result->max(fn($item) => (float) $item->MONTO_TOTAL);

            return response()->json([
                'data' => $result,
                'maxMonto' => $maxMonto,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en reportAreas', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Error inesperado al obtener reporte por áreas',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}
