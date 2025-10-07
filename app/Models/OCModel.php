<?php

namespace App\Models;

use App\Traits\HasDynamicConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OCModel extends Model
{
    use HasFactory, HasDynamicConnection;

    protected $table = 'COMOVC';

    public $timestamps = false;


    public function products()
    {
        return $this->hasMany(OCDModel::class, 'OC_CNUMORD', 'OC_CNUMORD');
    }

    public function responsible()
    {
        return $this->belongsTo(Responsible::class, 'OC_CSOLICT', 'RESPONSABLE_CODIGO');
    }

    public function required()
    {
        return $this->belongsTo(Required::class, 'OC_SOLICITA', 'TCLAVE');
    }

    public static function getOrderWithProducts($connectionName, $orderId)
    {
        $order = self::on($connectionName)->where('OC_CNUMORD', $orderId)->with('responsible')->with('required')->first();

        if ($order) {
            $order->setRelation('products', OCDModel::on($connectionName)
                ->where('OC_CNUMORD', $order->OC_CNUMORD)
                ->get());
        }

        return $order;
    }

    public static function getAllOrdersWithProducts(string $connectionName)
    {
        // Cambiamos la conexión por defecto del modelo relacionado antes de la consulta
        (new OCDModel)->setConnection($connectionName);

        return self::on($connectionName)
            ->with('products') // Ya no necesitas pasarle nada
            ->with('responsible')
            ->get();
    }

    public function getAllOC()
    {
        return $this->setConnection($this->connection)
            ->select('OC_CNUMORD', 'OC_CCODPRO', 'OC_CRAZSOC', 'OC_COBSERV', 'OC_CSOLICT', 'OC_CSITORD', 'OC_DFECENT', 'TipoDocumento', 'NOMBRE_USUARIO')
            ->get();
    }

    public static function getOrdersSummary(
        string $connectionName,
        string $dateStart,
        string $dateEnd,
        ?string $responsible = null,
        ?string $type = null,
        ?string $area = null
    ) {
        if ($type === 'OC') {
            $tables = ['COMOVC'];
        } elseif ($type === 'OS') {
            $tables = ['COMOVC_S'];
        } else {
            $tables = ['COMOVC', 'COMOVC_S'];
        }

        $results = collect();

        foreach ($tables as $table) {
            $rows = self::on($connectionName)
                ->from($table)
                ->join('MAEPROV as P', "$table.OC_CCODPRO", '=', 'P.PRVCCODIGO');

            // Relación con REQUISC depende del tipo
            if ($table === 'COMOVC') {
                $rows->join('REQUISC as R', "$table.OC_CNRODOCREF", '=', 'R.NROREQUI')
                    ->where('R.TIPOREQUI', 'RQ');
            } else {
                $rows->join('REQUISC as R', "$table.OC_CNRODOCREF", '=', 'R.NROREQUI')
                    ->where('R.TIPOREQUI', 'RS');
            }

            $rows->selectRaw('
                P.PRVCCODIGO as PROVEEDOR,
                P.PRVCNOMBRE as NOMBRE_PROVEEDOR,
                R.AREA as AREA,
                SUM(' . $table . '.OC_NVENTA) as MONTO_TOTAL
            ')
                ->whereIn("$table.OC_CSITORD", ['01', '03', '04'])
                ->whereBetween("$table.OC_DFECDOC", [$dateStart, $dateEnd]);

            if ($responsible) {
                $rows->where("$table.OC_CSOLICT", $responsible);
            }

            if ($area) {
                $rows->where('R.AREA', $area);
            }

            $rows = $rows->groupBy('P.PRVCCODIGO', 'P.PRVCNOMBRE', 'R.AREA')->get();

            $results = $results->concat($rows);
        }

        return $results
            ->groupBy('PROVEEDOR')
            ->map(function ($items) {
                return (object)[
                    'PROVEEDOR' => $items->first()->PROVEEDOR,
                    'NOMBRE_PROVEEDOR' => $items->first()->NOMBRE_PROVEEDOR,
                    'MONTO_TOTAL' => $items->sum('MONTO_TOTAL'),
                ];
            })
            ->sortByDesc('MONTO_TOTAL')
            ->values();
    }


    public static function reportAreas(
        string $connectionName,
        string $dateStart,
        string $dateEnd,
        ?string $responsible = null,
        ?string $type = null
    ) {
        if ($type === 'OC') {
            $tables = ['COMOVC'];
        } elseif ($type === 'OS') {
            $tables = ['COMOVC_S'];
        } else {
            $tables = ['COMOVC', 'COMOVC_S'];
        }

        $results = collect();

        foreach ($tables as $table) {
            $query = self::on($connectionName)
                ->from($table)
                ->join('REQUISC as R', function ($join) use ($table) {
                    $join->on("$table.OC_CNRODOCREF", '=', 'R.NROREQUI');

                    if ($table === 'COMOVC') {
                        $join->where('R.TIPOREQUI', '=', 'RQ');
                    } else {
                        $join->where('R.TIPOREQUI', '=', 'RS');
                    }
                })
                ->join('AREA as A', 'R.AREA', '=', 'A.AREA_CODIGO')
                ->selectRaw("
            A.AREA_CODIGO as id,
            A.AREA_DESCRIPCION as name,
            SUM({$table}.OC_NVENTA) as MONTO_TOTAL
        ")
                ->whereIn("{$table}.OC_CSITORD", ['01', '03', '04'])
                ->whereBetween("{$table}.OC_DFECDOC", [$dateStart, $dateEnd]);

            if ($responsible) {
                $query->where("{$table}.OC_CSOLICT", $responsible);
            }

            $rows = $query->groupBy('A.AREA_CODIGO', 'A.AREA_DESCRIPCION')->get();

            $results = $results->concat($rows);
        }

        return $results
            ->groupBy('id')
            ->map(function ($items) {
                return (object)[
                    'id' => $items->first()->id,
                    'name' => $items->first()->name,
                    'MONTO_TOTAL' => $items->sum('MONTO_TOTAL'),
                ];
            })
            ->sortByDesc('MONTO_TOTAL')
            ->values();
    }

    public static function reportAreasByOrders(
        string $connectionName,
        string $dateStart,
        string $dateEnd,
        ?string $responsible = null,
        ?string $type = null
    ) {
        if ($type === 'OC') {
            $tables = ['COMOVC'];
        } elseif ($type === 'OS') {
            $tables = ['COMOVC_S'];
        } else {
            $tables = ['COMOVC', 'COMOVC_S'];
        }

        $results = collect();

        foreach ($tables as $table) {
            $query = self::on($connectionName)
                ->from($table)
                ->join('REQUISC as R', function ($join) use ($table) {
                    $join->on("$table.OC_CNRODOCREF", '=', 'R.NROREQUI');

                    if ($table === 'COMOVC') {
                        $join->where('R.TIPOREQUI', '=', 'RQ');
                    } else {
                        $join->where('R.TIPOREQUI', '=', 'RS');
                    }
                })
                ->join('AREA as A', 'R.AREA', '=', 'A.AREA_CODIGO')
                ->selectRaw("
                A.AREA_CODIGO as id,
                A.AREA_DESCRIPCION as name,
                COUNT(*) as TOTAL_ORDERS
            ")
                ->whereIn("{$table}.OC_CSITORD", ['01', '03', '04'])
                ->whereBetween("{$table}.OC_DFECDOC", [$dateStart, $dateEnd]);

            if ($responsible) {
                $query->where("{$table}.OC_CSOLICT", $responsible);
            }

            $rows = $query->groupBy('A.AREA_CODIGO', 'A.AREA_DESCRIPCION')->get();

            $results = $results->concat($rows);
        }

        return $results
            ->groupBy('id')
            ->map(function ($items) {
                return (object)[
                    'id' => $items->first()->id,
                    'name' => $items->first()->name,
                    'MONTO_TOTAL' => $items->sum('TOTAL_ORDERS'),
                ];
            })
            ->sortByDesc('TOTAL_ORDERS')
            ->values();
    }

    public static function reportMonthlyExpenses(
        string $connectionName,
        ?string $type = null,
        ?string $responsible = null,
        ?string $area = null,
        int $monthsBack = 5
    ) {
        // Rango de fechas
        $dateEnd = now()->endOfMonth()->toDateString();
        $dateStart = now()->subMonths($monthsBack - 1)->startOfMonth()->toDateString();

        // --- Consulta OC ---
        $ocQuery = self::on($connectionName)
            ->from('COMOVC')
            ->join('REQUISC as R', 'COMOVC.OC_CNRODOCREF', '=', 'R.NROREQUI')
            ->where('R.TIPOREQUI', 'RQ')
            ->selectRaw("FORMAT(COMOVC.OC_DFECDOC, 'yyyy-MM') as month, SUM(COMOVC.OC_NVENTA) as oc_total")
            ->whereIn('COMOVC.OC_CSITORD', ['01', '03', '04'])
            ->whereBetween('COMOVC.OC_DFECDOC', [$dateStart, $dateEnd]);

        if ($responsible) $ocQuery->where('COMOVC.OC_CSOLICT', $responsible);
        if ($area) $ocQuery->where('R.AREA', $area);

        $ocData = $ocQuery->groupByRaw("FORMAT(COMOVC.OC_DFECDOC, 'yyyy-MM')")
            ->orderByRaw("FORMAT(COMOVC.OC_DFECDOC, 'yyyy-MM')")
            ->get();

        // --- Consulta OS ---
        $osQuery = self::on($connectionName)
            ->from('COMOVC_S')
            ->join('REQUISC as R', 'COMOVC_S.OC_CNRODOCREF', '=', 'R.NROREQUI')
            ->where('R.TIPOREQUI', 'RS')
            ->selectRaw("FORMAT(COMOVC_S.OC_DFECDOC, 'yyyy-MM') as month, SUM(COMOVC_S.OC_NVENTA) as os_total")
            ->whereIn('COMOVC_S.OC_CSITORD', ['01', '03', '04'])
            ->whereBetween('COMOVC_S.OC_DFECDOC', [$dateStart, $dateEnd]);

        if ($responsible) $osQuery->where('COMOVC_S.OC_CSOLICT', $responsible);
        if ($area) $osQuery->where('R.AREA', $area);

        $osData = $osQuery->groupByRaw("FORMAT(COMOVC_S.OC_DFECDOC, 'yyyy-MM')")
            ->orderByRaw("FORMAT(COMOVC_S.OC_DFECDOC, 'yyyy-MM')")
            ->get();

        // --- Combinar ambos ---
        $months = collect([...$ocData, ...$osData])
            ->pluck('month')
            ->unique()
            ->sort()
            ->values();

        $result = $months->map(function ($month) use ($ocData, $osData) {
            $oc = $ocData->firstWhere('month', $month)->oc_total ?? 0;
            $os = $osData->firstWhere('month', $month)->os_total ?? 0;
            return [
                'month' => $month,
                'oc_total' => (float) $oc,
                'os_total' => (float) $os,
            ];
        });

        return $result;
    }
}
