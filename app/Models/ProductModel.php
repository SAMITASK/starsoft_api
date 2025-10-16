<?php

namespace App\Models;

use App\Models\Product\FamilyModel;
use App\Models\Product\TypeModel;
use App\Traits\HasDynamicConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductModel extends Model
{
    use HasFactory, HasDynamicConnection;

    protected $table = 'MAEART';
    protected $primaryKey = 'ACODIGO';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'ACODIGO',
        'ADESCRI',
        'AUNIDAD',
        'AFSERIE',
        'AFLOTE',
        'AFAMILIA',
        'AMODELO',
        'AGRUPO',
        'ATIPO',
        'AUSER',
        'AFECHA',
    ];

    // ============================================
    // RELACIONES
    // ============================================

    public function type()
    {
        return $this->belongsTo(TypeModel::class, 'ATIPO', 'COD_TIPO');
    }

    public function family()
    {
        return $this->belongsTo(FamilyModel::class, 'AFAMILIA', 'FAM_CODIGO');
    }

    public function oc()
    {
        return $this->hasMany(OCDModel::class, 'OC_CCODIGO', 'ACODIGO');
    }

    public function os()
    {
        return $this->hasMany(OCSDModel::class, 'OC_CODSERVICIO', 'ACODIGO');
    }

    // ============================================
    // CONFIGURACIÓN DE TIPOS DE ORDEN
    // ============================================

    private const ORDER_TYPES = [
        'OC' => [
            'header' => 'COMOVC',
            'detail' => 'COMOVD',
            'tiporequi' => 'RQ',
            'is_service' => false,
        ],
        'OS' => [
            'header' => 'COMOVC_S',
            'detail' => 'COMOVD_S',
            'tiporequi' => 'RS',
            'is_service' => true,
        ],
    ];

    // ============================================
    // MÉTODO PRINCIPAL (PÚBLICO)
    // ============================================

    /**
     * Obtiene órdenes con productos agrupados por proveedor
     * 
     * @param string $connectionName Nombre de la conexión (ej: 'sqlsrv_003')
     * @param string $start Fecha inicio (Y-m-d)
     * @param string $end Fecha fin (Y-m-d)
     * @param string $area Código de área
     * @param string|null $responsible Código del responsable (opcional)
     * @param string|null $type Tipo de orden: 'OC', 'OS', 'ALL' o null
     * @return Collection
     */
    public static function getOrdersByAreaWithProducts(
        string $connectionName,
        string $start,
        string $end,
        string $area,
        ?string $responsible = null,
        ?string $type = null
    ): Collection {
        $tables = self::getTablesByType($type);
        $results = collect();

        foreach ($tables as $config) {
            $query = self::buildOrderQuery(
                $connectionName,
                $config,
                $start,
                $end,
                $area,
                $responsible
            );

            $results = $results->concat($query->get());
        }

        return self::aggregateResults($results);
    }

    // ============================================
    // MÉTODOS PRIVADOS (LÓGICA INTERNA)
    // ============================================

    /**
     * Determina qué tablas consultar según el tipo
     */
    private static function getTablesByType(?string $type): array
    {
        // Si es null o 'ALL', traer ambos tipos
        if (!$type || $type === 'ALL') {
            return array_values(self::ORDER_TYPES);
        }

        // Si el tipo existe en la configuración, devolverlo
        if (isset(self::ORDER_TYPES[$type])) {
            return [self::ORDER_TYPES[$type]];
        }

        // Fallback: traer ambos (por si acaso)
        return array_values(self::ORDER_TYPES);
    }

    /**
     * Construye la query para un tipo de orden específico
     */
    private static function buildOrderQuery(
        string $connectionName,
        array $config,
        string $start,
        string $end,
        string $area,
        ?string $responsible
    ) {
        $header = $config['header'];
        $detail = $config['detail'];

        $query = DB::connection($connectionName)
            ->table("{$header} as H")
            ->join("{$detail} as D", 'H.OC_CNUMORD', '=', 'D.OC_CNUMORD')
            ->join('MAEPROV as P', 'H.OC_CCODPRO', '=', 'P.PRVCCODIGO')
            ->join('REQUISC as R', 'H.OC_CNRODOCREF', '=', 'R.NROREQUI')
            ->select(self::getSelectFields($config['is_service']))
            ->whereIn('H.OC_CSITORD', ['01', '03', '04'])
            ->whereBetween('H.OC_DFECDOC', [$start, $end])
            ->whereRaw('CAST(R.AREA AS INT) = ?', [(int)$area])
            ->where('R.TIPOREQUI', $config['tiporequi']);

        if ($responsible) {
            $query->where('H.OC_CSOLICT', $responsible);
        }

        return $query;
    }

    /**
     * Define los campos SELECT según si es servicio o producto
     */
    private static function getSelectFields(bool $isService): array
    {
        $baseFields = [
            'P.PRVCCODIGO as proveedor_id',
            'P.PRVCNOMBRE as proveedor_name',
        ];

        if ($isService) {
            return array_merge($baseFields, [
                'D.OC_CODSERVICIO as product_id',
                'D.OC_CDESREF as product_name',
                DB::raw("'UND' as unidad"),
                'D.OC_CANT as cantidad',
                'D.OC_NPREUNI as precio_unitario',
                'D.OC_NPRENET as total',
                'D.OC_NIGV as igv',
            ]);
        }

        return array_merge($baseFields, [
            'D.OC_CCODIGO as product_id',
            'D.OC_CDESREF as product_name',
            'D.OC_CUNIDAD as unidad',
            'D.OC_NCANTID as cantidad',
            'D.OC_NPREUNI as precio_unitario',
            'D.OC_NTOTNET as total',
            'D.OC_NIGV as igv',
        ]);
    }

    /**
     * Agrupa y suma los resultados por proveedor y producto
     */
    private static function aggregateResults(Collection $results): Collection
    {
        return $results
            ->groupBy('proveedor_id')
            ->flatMap(function ($providerItems) {
                $providerId = $providerItems->first()->proveedor_id;
                $providerName = $providerItems->first()->proveedor_name;

                return $providerItems
                    ->groupBy('product_id')
                    ->map(function ($products) use ($providerId, $providerName) {
                        return self::buildProductSummary($products, $providerId, $providerName);
                    })
                    ->values();
            })
            ->values();
    }

    /**
     * Construye el resumen de un producto agrupado
     */
    private static function buildProductSummary(
        Collection $products,
        string $providerId,
        string $providerName
    ): array {
        $first = $products->first();
        $totalCantidad = $products->sum('cantidad');
        $totalMonto = $products->sum('total');
        $precioPromedio = $products->avg('precio_unitario');

        // ✅ Calcular IGV solo si el producto lo lleva
        $igvPromedio = $products->avg('igv');
        $precioConIgv = ($igvPromedio > 0)
            ? round($precioPromedio * 1.18, 2)
            : 0;

        return [
            'proveedor_id' => $providerId,
            'proveedor_name' => $providerName,
            'product_id' => $first->product_id,
            'product_name' => $first->product_name,
            'unidad' => $first->unidad,
            'cantidad' => round($totalCantidad, 2),
            'precio_unitario' => round($precioPromedio, 2),
            'precio_igv' => $precioConIgv,
            'total' => round($totalMonto, 2),
        ];
    }
}
