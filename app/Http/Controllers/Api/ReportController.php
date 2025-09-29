<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
                $userCode = CompanyUserPivot::where('user_id', auth()->id())
                    ->where('company_id', $request->input('company', '003'))
                    ->value('user_code');

                $responsible = $userCode;
            }

            $result = OCModel::getOrdersSummary($conexion, $start, $end, $responsible, $type);

            // Calcular monto máximo
            $maxMonto = $result->max(fn($item) => (float) $item->MONTO_TOTAL);

            return response()->json([
                'data' => $result,
                'maxMonto' => $maxMonto,
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

    public function reportSuppliersByArea(Request $request)
    {
        try {
            $conexion = 'sqlsrv_' . $request->input('company', '003');
            $dateRange = $request->input('date');
            $type = $request->input('type', 'OC');
            $area = $request->input('area');

            // Validar rango de fechas
            if (strpos($dateRange, ' a ') !== false) {
                [$start, $end] = explode(' a ', $dateRange);
                $start = Carbon::parse(trim($start))->format('Y-m-d');
                $end = Carbon::parse(trim($end))->format('Y-m-d');

                if ($start > $end) {
                    [$start, $end] = [$end, $start];
                }
            }

            // Determinar usuario responsable
            $responsible = null;
            if (auth()->check()) {
                $userCode = CompanyUserPivot::where('user_id', auth()->id())
                    ->where('company_id', $request->input('company', '003'))
                    ->value('user_code');

                $responsible = $userCode;
            }

            // Obtener resultados filtrados también por área
            $result = OCModel::reportSuppliersByArea(
                $conexion,
                $start,
                $end,
                $responsible,
                $type,
                $area
            );

            $maxMonto = $result->max(fn($item) => (float) $item->MONTO_TOTAL);

            return response()->json([
                'data' => $result,
                'maxMonto' => $maxMonto,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en reportSuppliersByArea', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Error inesperado al obtener proveedores por área',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}
