<?php

namespace App\Models;

use App\Models\Product\FamilyModel;
use App\Models\Product\TypeModel;
use App\Traits\HasDynamicConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductModel extends Model
{
    use HasFactory, HasDynamicConnection;

    protected $table = 'MAEART';
    protected $primaryKey = 'ACODIGO';
    public $incrementing = false; // varchar
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'ACODIGO',   // Código
        'ADESCRI',   // Descripción
        'AUNIDAD',   // Unidad
        'AFSERIE',   // Serie
        'AFLOTE',    // Lote
        'AFAMILIA',  // Familia
        'AMODELO',   // Línea / Modelo
        'AGRUPO',    // Grupo
        'ATIPO',     // Tipo
        'AUSER',     // Usuario
        'AFECHA',    // Fecha de registro
    ];

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

    public static function getOrdersByAreaWithProducts(
        string $connectionName,
        string $start,
        string $end,
        string $area,
        ?string $responsible = null,
        ?string $type = null
    ) {
        if ($type === 'OC') {
            $tables = [['header' => 'COMOVC', 'detail' => 'COMOVD', 'tiporequi' => 'RQ']];
        } elseif ($type === 'OS') {
            $tables = [['header' => 'COMOVC_S', 'detail' => 'COMOVD_S', 'tiporequi' => 'RS']];
        } else {
            $tables = [
                ['header' => 'COMOVC', 'detail' => 'COMOVD', 'tiporequi' => 'RQ'],
                ['header' => 'COMOVC_S', 'detail' => 'COMOVD_S', 'tiporequi' => 'RS'],
            ];
        }

        $results = collect();

        foreach ($tables as $table) {
            $header = $table['header'];
            $detail = $table['detail'];
            $tiporequi = $table['tiporequi'];

            $query = self::on($connectionName)
                ->from($header . ' as H')
                ->join($detail . ' as D', "H.OC_CNUMORD", '=', "D.OC_CNUMORD")
                ->join('MAEPROV as P', "H.OC_CCODPRO", '=', 'P.PRVCCODIGO')
                ->join('REQUISC as R', "H.OC_CNRODOCREF", '=', "R.NROREQUI")
                ->selectRaw(
                    $header === 'COMOVC'
                        ? "P.PRVCCODIGO as proveedor_id,
                       P.PRVCNOMBRE as proveedor_name,
                       D.OC_CCODIGO as product_id,
                       D.OC_CDESREF as product_name,
                       D.OC_CUNIDAD as unidad,
                       D.OC_NCANTID as cantidad,
                       D.OC_NPREUNI as precio_unitario,
                       D.OC_NPRENET as total"
                        : "P.PRVCCODIGO as proveedor_id,
                       P.PRVCNOMBRE as proveedor_name,
                       D.OC_CODSERVICIO as product_id,
                       D.OC_CDESREF as product_name,
                       'UND' as unidad,
                       D.OC_CANT as cantidad,
                       D.OC_NPREUNI as precio_unitario,
                       D.OC_NPRENET as total"
                )
                ->whereIn('H.OC_CSITORD', ['01', '03', '04'])
                ->whereBetween('H.OC_DFECDOC', [$start, $end])
                ->whereRaw("CAST(R.AREA AS INT) = ?", [(int)$area])
                ->where('R.TIPOREQUI', $tiporequi);

            if ($responsible) {
                $query->where('H.OC_CSOLICT', $responsible);
            }

            $results = $results->concat($query->get());
        }

        return $results
            ->groupBy('proveedor_id')
            ->map(function ($items) {
                return [
                    'proveedor_id' => $items->first()->proveedor_id,
                    'proveedor_name' => $items->first()->proveedor_name,
                    'products' => $items
                        ->groupBy('product_id')
                        ->map(function ($products) {
                            $totalCantidad = $products->sum('cantidad');
                            $totalMonto = $products->sum('total');
                            $precioPromedio = $products->avg('precio_unitario');
                            $precioConIGV   = round($precioPromedio * 1.18, 2);

                            return [
                                'product_id' => $products->first()->product_id,
                                'product_name' => $products->first()->product_name,
                                'unidad' => $products->first()->unidad,
                                'cantidad' => $totalCantidad,
                                'precio_unitario' => round($precioPromedio, 2),
                                'precio_igv'      => round($precioConIGV),
                                'total' => $totalMonto,
                            ];
                        })
                        ->values(),
                ];
            })
            ->values();
    }
}
