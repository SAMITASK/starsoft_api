<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanyModel;
use App\Models\OCDModel;
use App\Models\OCSDModel;
use App\Models\ProductModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\DateHelper;

class ProductController extends Controller
{
    public function getProducts(Request $request)
    {
        try {
            $conexion = 'sqlsrv_' . $request->input('company');
            $table = (new ProductModel())->getTable();

            $query = ProductModel::on($conexion)
                ->leftJoin('TIPO_ARTICULO', 'TIPO_ARTICULO.COD_TIPO', '=', $table . '.ATIPO')
                ->leftJoin('FAMILIA', 'FAMILIA.FAM_CODIGO', '=', $table . '.AFAMILIA')
                ->select(
                    $table . '.*',
                    'TIPO_ARTICULO.DES_TIPO as type_name',
                    'FAMILIA.FAM_NOMBRE as family_name'
                );

            $this->applyFilters($query, $request);
            $this->applySorting($query, $request, $table);

            $itemsPerPage = max((int) $request->input('itemsPerPage', 10), 1);
            $results = $query->paginate($itemsPerPage);

            return response()->json([
                'products'  => $this->formatProducts($results->items()),
                'total' => $results->total(),
                'per_page' => $results->perPage(),
                'current_page' => $results->currentPage(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Error inesperado al obtener productos',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q2) use ($search) {
                $q2->where('ACODIGO', 'like', "%$search%")
                    ->orWhere('ADESCRI', 'like', "%$search%")
                    ->orWhere('AUNIDAD', 'like', "%$search%");
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

                $query->whereBetween(DB::raw("CONVERT(DATE, AFECHA)"), [$start, $end]);
            } else {
                $date = Carbon::parse(trim($dateRange))->format('Y-m-d');
                $query->whereDate(DB::raw("CONVERT(DATE, AFECHA)"), $date);
            }
        }

        // Filtro por tipo (opcional)
        if ($request->filled('type')) {
            $query->where('ATIPO', $request->input('type'));
        }

        // Filtro por familia (opcional)
        if ($request->filled('family')) {
            $query->where('AFAMILIA', $request->input('family'));
        }
    }

    private function applySorting($query, Request $request, $table): void
    {
        $sortBy = $request->input('sortBy');
        $orderBy = $request->input('orderBy', 'asc');

        $allowedSorts = [
            'code'        => $table . '.ACODIGO',
            'description' => $table . '.ADESCRI',
            'measure'     => $table . '.AUNIDAD',
            'serie'       => $table . '.AFSERIE',
            'batch'       => $table . '.AFLOTE',
            'family'      => $table . '.AFAMILIA',
            'family_name'  => 'FAMILIA.FAM_NOMBRE',
            'line'        => $table . '.AMODELO',
            'group'       => $table . '.AGRUPO',
            'type'        => $table . '.ATIPO',
            'type_name'   => 'TIPO_ARTICULO.DES_TIPO', // 👈 importante
            'user'        => $table . '.AUSER',
            'date'        => $table . '.AFECHA',
        ];

        if ($sortBy && isset($allowedSorts[$sortBy])) {
            $query->orderBy($allowedSorts[$sortBy], $orderBy === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderByDesc($table . '.AFECHA'); // por defecto
        }
    }

    private function formatProducts(array $items): array
    {
        return collect($items)->map(function ($item) {
            return [
                'code'        => $item->ACODIGO,
                'description' => $item->ADESCRI,
                'measure'     => $item->AUNIDAD,
                'serie'       => $item->AFSERIE,
                'batch'       => $item->AFLOTE,
                'family'      => $item->AFAMILIA,
                'family_name' => $item->family_name,
                'line'        => $item->AMODELO,
                'group'       => $item->AGRUPO,
                'type'        => $item->ATIPO,
                'type_name'   => $item->type_name,
                'user'        => $item->AUSER,
                'date'        => $item->AFECHA ? Carbon::parse($item->AFECHA)->format('d/m/Y') : null,
            ];
        })->toArray();
    }

    public function getProduct(Request $request, $id)
    {

        try {
            $companyCode = $request->query('company');
            $conexion = 'sqlsrv_' . $companyCode;

            $product = ProductModel::on($conexion)
                ->leftJoin('TIPO_ARTICULO', 'TIPO_ARTICULO.COD_TIPO', '=', 'MAEART.ATIPO')
                ->leftJoin('FAMILIA', 'FAMILIA.FAM_CODIGO', '=', 'MAEART.AFAMILIA')
                ->select(
                    'MAEART.*',
                    'TIPO_ARTICULO.DES_TIPO as type_name',
                    'FAMILIA.FAM_NOMBRE as family_name'
                )
                ->where('MAEART.ACODIGO', $id)
                ->firstOrFail();

            // Obtener empresa (opcional)
            $company = CompanyModel::where('EMP_CODIGO', $companyCode)->first();
            $companyName = $company ? $company->EMP_RAZON_NOMBRE : 'Empresa desconocida';

            return response()->json([
                'product' => [
                    'code'        => $product->ACODIGO,
                    'description' => $product->ADESCRI,
                    'measure'     => $product->AUNIDAD,
                    'serie'       => $product->AFSERIE,
                    'batch'       => $product->AFLOTE,
                    'family'      => $product->AFAMILIA,
                    'family_name' => $product->family_name,
                    'line'        => $product->AMODELO,
                    'group'       => $product->AGRUPO,
                    'type'        => $product->ATIPO,
                    'type_name'   => $product->type_name,
                    'user'        => $product->AUSER,
                    'date'        => $product->AFECHA ? Carbon::parse($product->AFECHA)->format('d/m/Y') : null,
                ],
                'company_name' => $companyName,
                'stats' => [
                    'oc'  => 0,  // Valores iniciales, se actualizarán desde el frontend
                    'ocs' => 0,
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en getProduct', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'product_id' => $id,
                'company' => $companyCode ?? 'null'
            ]);

            return response()->json([
                'error' => 'Error al obtener el producto',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    private function countPurcharseOrders(string $connection, string $productCode): int
    {
        return OCDModel::on($connection)
            ->where('OC_CCODIGO', $productCode) // Ajusta este campo según tu DB de detalle
            ->count();
    }

    private function countServiceOrders(string $connection, $productCode): int
    {
        return OCSDModel::on($connection)
            ->where('OC_CODSERVICIO', $productCode)
            ->count();
    }

    public function getProductOrders(Request $request)
    {
        try {
            $conexion = 'sqlsrv_' . $request->query('company');
            $product = $request->query('product');
            $search = $request->query('q');
            $dateRange = $request->query('date');
            $type = $request->query('type'); // 'OC', 'OS' o 'ALL'

            // Parsear rango de fechas
            [$start, $end] = DateHelper::parseDateRange($dateRange);

            // Construir queries según el tipo
            $queries = match ($type) {
                'OC' => [
                    $this->buildProductOrderQuery(OCDModel::on($conexion), 'OC', $product, $search, $start, $end)
                ],
                'OS' => [
                    $this->buildProductOrderQuery(OCSDModel::on($conexion), 'OS', $product, $search, $start, $end)
                ],
                default => [
                    $this->buildProductOrderQuery(OCDModel::on($conexion), 'OC', $product, $search, $start, $end),
                    $this->buildProductOrderQuery(OCSDModel::on($conexion), 'OS', $product, $search, $start, $end)
                ]
            };

            // Unión de queries
            $union = array_shift($queries);
            foreach ($queries as $q) {
                $union->unionAll($q);
            }

            // Ejecutar unión
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
            Log::error('Error en getProductOrders', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'product_code' => $product,
            ]);

            return response()->json([
                'error' => 'No se pudieron obtener las órdenes del producto',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    private function buildProductOrderQuery($query, string $type, string $product, ?string $search, ?string $start, ?string $end)
    {
        if ($type === 'OC') {
            $detalleTable = 'COMOVD';   // tabla detalle OC
            $cabecera = 'COMOVC';        // tabla cabecera OC
            $productoColumn = 'OC_CCODIGO';
        } else {
            $detalleTable = 'COMOVD_S';  // tabla detalle OS
            $cabecera = 'COMOVC_S';      // tabla cabecera OS
            $productoColumn = 'OC_CODSERVICIO';
        }

        // Asignar alias al detalle
        $query->from("$detalleTable as d")
            ->join("$cabecera as o", "d.OC_CNUMORD", '=', "o.OC_CNUMORD") // unir detalle con cabecera
            ->selectRaw("
            '{$type}' as type,
            o.OC_CNUMORD as number,
            o.OC_COBSERV as observ,
            o.OC_CSOLICT as solicitante_codigo,
            r.RESPONSABLE_NOMBRE as responsable,
            ab.TDESCRI as solicita,
            o.OC_CSITORD as status,
            o.OC_DFECENT as fecha,
            TipoDocumento as tipo_doc,
            o.OC_CUSUARI as usuario,
            o.FECHAHORA_CAMBIOESTADO as issue_date
        ")
            ->leftJoin('RESPONSABLECMP as r', 'o.OC_CSOLICT', '=', 'r.RESPONSABLE_CODIGO')
            ->leftJoin('TABAYU as ab', 'o.OC_SOLICITA', '=', 'ab.TCLAVE')
            ->where("d.$productoColumn", $product); // FILTRO por producto en detalle

        // Búsqueda opcional
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('o.OC_CNUMORD', 'like', "%$search%")
                    ->orWhere('o.OC_CRAZSOC', 'like', "%$search%")
                    ->orWhere('o.OC_COBSERV', 'like', "%$search%")
                    ->orWhere('r.RESPONSABLE_NOMBRE', 'like', "%$search%");
            });
        }

        // Filtro por rango de fechas
        if ($start && $end) {
            $query->whereBetween('o.OC_DFECDOC', [$start, $end]);
        }

        return $query;
    }
}
