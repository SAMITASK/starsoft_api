<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OCModel;
use App\Models\OCSModel;
use App\Models\Orders;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrdersApi extends Controller
{
    public function getOrders(Request $request)
    {
        try {
            $query = Orders::query();

            // Aplicar filtros
            $this->applyFilters($query, $request);

            // Aplicar ordenamiento
            $this->applySorting($query, $request);

            // Paginación
            $itemsPerPage = max((int) $request->input('itemsPerPage', 10), 1);
            $results = $query->paginate($itemsPerPage);

            // Formatear resultados
            $formatted = $this->formatOrders($results->items());

            return response()->json([
                'ocs'   => $formatted,
                'total' => $results->total(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en getOrders', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Error inesperado al obtener órdenes',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('company')) {
            $query->where('codigoEmpresa', $request->input('company'));
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('estado', $request->input('status'));
        }

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($sub) use ($search) {
                $sub->where('identificador', 'like', "%$search%")
                    ->orWhere('asunto', 'like', "%$search%");
            });
        }

        if ($request->filled('date')) {
            $dateRange = $request->input('date');

            if (strpos($dateRange, ' a ') !== false) {
                [$start, $end] = explode(' a ', $dateRange);
                $start = Carbon::parse(trim($start))->format('Y-m-d');
                $end = Carbon::parse(trim($end))->format('Y-m-d');

                if ($start > $end) {
                    [$start, $end] = [$end, $start];
                }

                $query->whereBetween(DB::raw("CONVERT(DATE, fechaGeneracion)"), [$start, $end]);
            } else {
                $date = Carbon::parse(trim($dateRange))->format('Y-m-d');
                $query->whereDate(DB::raw("CONVERT(DATE, fechaGeneracion)"), $date);
            }
        }
    }


    private function applySorting($query, Request $request): void
    {
        $sortBy = $request->input('sortBy');
        $orderBy = $request->input('orderBy', 'asc');

        $allowedSorts = [
            'company'      => 'codigoEmpresa',
            'company_name' => 'nombreEmpresa',
            'module'       => 'modulo',
            'type'         => 'tipo',
            'code'         => 'identificador',
            'issue'        => 'asunto',
            'issue_date'   => 'fechaGeneracion',
            'status'       => 'estado',
        ];

        if ($sortBy && isset($allowedSorts[$sortBy])) {
            $query->orderBy($allowedSorts[$sortBy], $orderBy === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderByDesc('fechaGeneracion'); // Orden por defecto
        }
    }

    private function formatOrders(array $orders): array
    {
        return collect($orders)->map(function ($oc) {
            return [
                'company'       => $oc->codigoEmpresa,
                'company_name'  => $oc->nombreEmpresa,
                'module'        => $oc->modulo,
                'type'          => $oc->tipo,
                'code'          => $oc->identificador,
                'status'        => $oc->estado,
                'date'          => $oc->fechaGeneracion,
                'issue'         => $oc->asunto,
                'issue_date'    => $oc->fechaGeneracion
                    ? Carbon::parse($oc->fechaGeneracion)->format('Y-m-d')
                    : null,
                'read'          => (bool) $oc->leido, // <--- este campo
            ];
        })->toArray();
    }

    public function getOrder(Request $request)
    {
        $company = $request->input('company');
        $type = $request->input('type');
        $code = $request->input('code');

        $connection = 'sqlsrv_' . $company;

        if ($type == 'OC') {
            $order = OCModel::getOrderWithProducts($connection, $code);
        } elseif ($type == 'OS') {
            $order = OCSModel::getOrderWithProducts($connection, $code);
        } else {
            return response()->json(['message' => 'Tipo de orden inválido'], 400);
        }

        if (!$order) {
            return response()->json(['message' => 'Orden no encontrada'], 404);
        }

        return response()->json($order);
    }

    public function markAsRead(Request $request)
    {
        $oc = Orders::where('tipo', $request->type)
            ->where('identificador', $request->code)
            ->where('codigoEmpresa', $request->company)
            ->first();


        if (!$oc) {
            return response()->json(['error' => 'Orden no encontrada'], 404);
        }

        $oc->leido = 1;
        $oc->save();

        return response()->json(['success' => true]);
    }

    public function handleApproval(Request $request)
    {
        try {
            // Paso 1: actualiza BDWENCO
            $oc = Orders::where('tipo', $request->type)
                ->where('identificador', $request->code)
                ->where('codigoEmpresa', $request->company)
                ->firstOrFail();

            $estado = match ($request->action) {
                'approve' => 'APROBADA',
                'reject'  => 'RECHAZADA',
                default   => null,
            };

            $oc->estado = $estado;
            $oc->fechaAprobacion = now();
            $oc->save();

            // Paso 2: actualiza COMOVC o COMOVC_S en la misma conexión
            $connection = 'sqlsrv_' . $request->company;
            $table = $request->type === 'OC' ? 'dbo.COMOVC' : 'dbo.COMOVC_S';
            $fieldId = $request->type === 'OC' ? 'OC_CNUMORD' : 'OC_CNUMORD';
            $fieldStatus = $request->type === 'OC' ? 'OC_CSITORD' : 'OCS_CSITORD';

            $user = $request->user();

            DB::connection($connection)
                ->table($table)
                ->where($fieldId, $request->code)
                ->update([
                    $fieldStatus               => $request->action === 'approve' ? '01' : '06',
                    'NOMBRE_USUARIO'          => $user->name,
                    'CARGO_USUARIO'           => $user->cargo ?? '',
                    'FECHAHORA_CAMBIOESTADO'  => now(),
                ]);

            return response()->json([
                'success' => true,
                'estado'  => $estado,
                'message' => "La orden fue {$estado} correctamente",
                'color'   => 'primary',
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en handleApproval', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo procesar la orden',
                'color'   => 'error',
            ], 500);
        }
    }
}
