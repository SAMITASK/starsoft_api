<?php

namespace App\Models;

use App\Traits\HasDynamicConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory, HasDynamicConnection;

    protected $table = 'AREA';
    protected $primaryKey = 'AREA_CODIGO';
    protected $keyType = 'string';
    public $timestamps = false;


    public static function getAvailableAreas(
        string $connectionName,
        string $dateStart,
        string $dateEnd,
        ?string $responsible = null,
        ?string $type = null,
        ?array $areaFilter = null
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
            if (is_array($areaFilter) && empty($areaFilter)) {
                continue;
            }

            $rows = self::on($connectionName)
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
                ->select('A.AREA_CODIGO as id', 'A.AREA_DESCRIPCION as name')
                ->whereIn("$table.OC_CSITORD", ['01', '03', '04'])
                ->whereBetween("$table.OC_DFECDOC", [$dateStart, $dateEnd]);

            if ($responsible) {
                $rows->where("$table.OC_CSOLICT", $responsible);
            }

            if (is_array($areaFilter)) {
                $rows->whereIn('R.AREA', $areaFilter);
            }

            $results = $results->concat($rows->distinct()->get());
        }

        return $results->unique('id')->values();
    }
}
