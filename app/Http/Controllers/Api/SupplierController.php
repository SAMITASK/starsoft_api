<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OCModel;
use App\Models\OCSModel;
use App\Models\Supplier;
use App\Models\CompanyModel;
use App\Models\CompanyUserPivot;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SupplierController extends Controller
{
    public function getSuppliers(Request $request)
    {
        try {
            $conexion = 'sqlsrv_' . $request->input('company');
            $query = Supplier::on($conexion);

            $this->applyFilters($query, $request);
            $this->applySorting($query, $request);

            $itemsPerPage = max((int) $request->input('itemsPerPage', 10), 1);
            $results = $query->paginate($itemsPerPage);

            $formatted = $this->formatSuppliers($results->items());

            return response()->json([
                'suppliers' => $formatted,
                'total' => $results->total(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en getSuppliers', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Error inesperado al obtener proveedores',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q2) use ($search) {
                $q2->where('PRVCNOMBRE', 'like', "%$search%")
                    ->orWhere('PRVCRUC', 'like', "%$search%")
                    ->orWhere('PRVCUSER', 'like', "%$search%");
            });
        }

        if (!$request->boolean('ignoreDateFilter') && $request->filled('date')) {
            $dateRange = $request->input('date');

            if (strpos($dateRange, ' a ') !== false) {
                [$start, $end] = explode(' a ', $dateRange);
                $start = Carbon::parse(trim($start))->format('Y-m-d');
                $end = Carbon::parse(trim($end))->format('Y-m-d');

                if ($start > $end) {
                    [$start, $end] = [$end, $start];
                }

                $query->whereBetween(DB::raw("CONVERT(DATE, PRVDFECCRE)"), [$start, $end]);
            } else {
                $date = Carbon::parse(trim($dateRange))->format('Y-m-d');
                $query->whereDate(DB::raw("CONVERT(DATE, PRVDFECCRE)"), $date);
            }
        }
    }

    private function applySorting($query, Request $request): void
    {
        $sortBy = $request->input('sortBy');
        $orderBy = $request->input('orderBy', 'asc');

        $allowedSorts = [
            'ruc'        => 'PRVCRUC',
            'reason'     => 'PRVCNOMBRE',
            'address'    => 'PRVCDIRECC',
            'issue_date' => 'PRVDFECCRE',
            'user'       => 'PRVCUSER',
        ];

        if ($sortBy && isset($allowedSorts[$sortBy])) {
            $query->orderBy($allowedSorts[$sortBy], $orderBy === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderByDesc('PRVDFECCRE'); // orden por defecto
        }
    }

    private function formatSuppliers(array $items): array
    {
        return collect($items)->map(function ($item) {
            return [
                'code'        => $item->PRVCCODIGO,
                'ruc'        => $item->PRVCRUC,
                'reason'     => $item->PRVCNOMBRE,
                'address'    => $item->PRVCDIRECC,
                'issue_date' => $item->PRVDFECCRE
                    ? Carbon::parse($item->PRVDFECCRE)->format('Y-m-d')
                    : null,
                'user'       => $item->PRVCUSER,
            ];
        })->toArray();
    }

    public function getSupplier(Request $request, $id)
    {
        try {
            $companyCode = $request->query('company');
            $conexion = 'sqlsrv_' . $companyCode;

            // Buscar proveedor
            $supplier = Supplier::on($conexion)->findOrFail($id);

            // Buscar nombre de la empresa
            $company = CompanyModel::where('EMP_CODIGO', $companyCode)->first();
            $companyName = $company ? $company->EMP_RAZON_NOMBRE : 'Empresa desconocida';

            // Cantidades
            $orderCount = $this->countPurchaseOrders($conexion, $id);
            $serviceOrderCount = $this->countServiceOrders($conexion, $id);

            return response()->json([
                'ruc'        => $supplier->PRVCRUC,
                'reason'     => $supplier->PRVCNOMBRE,
                'address'    => $supplier->PRVCDIRECC,
                'issue_date' => $supplier->PRVDFECCRE
                    ? Carbon::parse($supplier->PRVDFECCRE)->format('Y-m-d')
                    : null,
                'user'       => $supplier->PRVCUSER,
                'purchase_orders_count' => $orderCount,
                'service_orders_count' => $serviceOrderCount,
                'company_name' => $companyName,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en getSupplier', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Error inesperado al obtener proveedor',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    private function countPurchaseOrders(string $connection, $ruc): int
    {
        return OCModel::on($connection)
            ->where('OC_CCODPRO', $ruc)
            ->count();
    }

    private function countServiceOrders(string $connection, $ruc): int
    {
        return OCSModel::on($connection)
            ->where('OC_CCODPRO', $ruc)
            ->count();
    }

    public function getSupplierOrders(Request $request)
    {
        try {
            $conexion = 'sqlsrv_' . $request->query('company');
            $supplier = $request->query('supplier');
            $search = $request->query('q');

            $ocQuery = $this->buildOrderQuery(OCModel::on($conexion), 'OC', $supplier, $search);
            $ocsQuery = $this->buildOrderQuery(OCSModel::on($conexion), 'OS', $supplier, $search);

            // Unión
            $union = $ocQuery->unionAll($ocsQuery);

            $orders = DB::connection($conexion)
                ->table(DB::raw("({$union->toSql()}) as combined"))
                ->mergeBindings($union->getQuery())
                ->orderBy('fecha', 'desc')
                ->get();

            return response()->json([
                'orders' => $orders,
                'total'  => $orders->count(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en getSupplierOrders', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'No se pudieron obtener las órdenes',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    private function buildOrderQuery($query, string $type, string $supplier, ?string $search)
    {
        $query->selectRaw("
        '{$type}' as type,
        OC_CNUMORD as number,
        OC_COBSERV as observ,
        OC_CSOLICT as solicitante_codigo,
        r.RESPONSABLE_NOMBRE as responsable,
        ab.TDESCRI as solicita,
        OC_CSITORD as status,
        OC_DFECENT as fecha,
        TipoDocumento as tipo_doc,
        OC_CUSUARI as usuario,
        FECHAHORA_CAMBIOESTADO as issue_date
    ")
            ->leftJoin('RESPONSABLECMP as r', 'OC_CSOLICT', '=', 'r.RESPONSABLE_CODIGO')
            ->leftJoin('TABAYU as ab', 'OC_SOLICITA', '=', 'ab.TCLAVE')
            ->where('OC_CCODPRO', $supplier);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('OC_CNUMORD', 'like', "%$search%")
                    ->orWhere('OC_CRAZSOC', 'like', "%$search%")
                    ->orWhere('OC_COBSERV', 'like', "%$search%")
                    ->orWhere('r.RESPONSABLE_NOMBRE', 'like', "%$search%");
            });
        }

        return $query;
    }

    
}
