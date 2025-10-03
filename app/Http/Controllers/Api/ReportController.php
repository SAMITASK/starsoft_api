<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\CompanyUserPivot;
use App\Models\OCModel;
use App\Models\ProductModel;
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

                if (in_array(strtoupper($user->cargo), ['GERENTE', 'ADMINISTRADOR'])) {
                    $responsible = $staff;
                } else {
                    $userCode = CompanyUserPivot::where('user_id', $user->id)
                        ->where('company_id', $request->input('company', '003'))
                        ->value('user_code');

                    $responsible = $userCode;
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

            if (strpos($dateRange, ' a ') !== false) {
                [$start, $end] = explode(' a ', $dateRange);
                $start = Carbon::parse(trim($start))->format('Y-m-d');
                $end = Carbon::parse(trim($end))->format('Y-m-d');

                if ($start > $end) {
                    [$start, $end] = [$end, $start];
                }
            } else {
                // fallback si solo llega una fecha
                $start = Carbon::parse(trim($dateRange))->format('Y-m-d');
                $end = $start;
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

    public function reportSupplierProductsAreas(Request $request)
    {

        try {
            $conexion = 'sqlsrv_' . $request->input('company', '003');
            $area = $request->input('area');
            $dateRange = $request->input('date');
            $type = $request->input('type', 'OC');

            // Validar rango de fechas
            if (strpos($dateRange, ' a ') !== false) {
                [$start, $end] = explode(' a ', $dateRange);
                $start = Carbon::parse(trim($start))->format('Y-m-d');
                $end = Carbon::parse(trim($end))->format('Y-m-d');

                if ($start > $end) {
                    [$start, $end] = [$end, $start];
                }
            } else {
                // fallback si solo llega una fecha
                $start = Carbon::parse(trim($dateRange))->format('Y-m-d');
                $end = $start;
            }

            $responsible = null;
            if (auth()->check()) {
                $user = auth()->user();

                if (!in_array(strtoupper($user->cargo), ['GERENTE', 'ADMINISTRADOR'])) {
                    $userCode = CompanyUserPivot::where('user_id', $user->id)
                        ->where('company_id', $request->input('company', '003'))
                        ->value('user_code');

                    $responsible = $userCode;
                }
            }

            // Llamada al modelo
            $result = ProductModel::getOrdersByAreaWithProducts(
                $conexion,
                $start,
                $end,
                $area,
                $responsible,
                $type
            );

            // Formatear para el front
            $formatted = $this->formatSupplierProducts($result);

            return response()->json([
                'suppliers' => $formatted,
                'total' => count($formatted),
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en reportSupplierProductsAreas', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Error inesperado al obtener órdenes por proveedor y productos',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    private function formatSupplierProducts($items): array
    {
        return collect($items)->map(function ($item) {
            return [
                'proveedor_id'   => $item['proveedor_id'],
                'proveedor_name' => $item['proveedor_name'],
                'product_id'     => $item['product_id'],
                'product_name'   => $item['product_name'],
                'unidad'         => $item['unidad'],
                'cantidad'       => $item['cantidad'],
                'precio_unitario' => $item['precio_unitario'],
                'precio_igv'     => $item['precio_igv'],
                'total'          => $item['total'],
            ];
        })->toArray();
    }

    public function reportAreasByOrders(Request $request)
    {
        try {
            $conexion = 'sqlsrv_' . $request->input('company', '003');
            $dateRange = $request->input('date');
            $type = $request->input('type', 'OC');
            $staff = $request->input('staff');

            // Manejo de fechas
            if (strpos($dateRange, ' a ') !== false) {
                [$start, $end] = explode(' a ', $dateRange);
                $start = Carbon::parse(trim($start))->format('Y-m-d');
                $end = Carbon::parse(trim($end))->format('Y-m-d');

                if ($start > $end) {
                    [$start, $end] = [$end, $start];
                }
            } else {
                $start = Carbon::parse(trim($dateRange))->format('Y-m-d');
                $end = $start;
            }

            // Responsable
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

            $result = OCModel::reportAreasByOrders(
                $conexion,
                $start,
                $end,
                $responsible,
                $type
            );

            $maxCantidad = $result->max(fn($item) => (int) $item->MONTO_TOTAL);

            return response()->json([
                'data' => $result,
                'maxCantidad' => $maxCantidad,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en reportAreasCount', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Error inesperado al obtener reporte por áreas (conteo)',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}
