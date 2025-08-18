<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        if ($request->filled('date')) {
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
}
