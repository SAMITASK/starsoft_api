<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OCModel;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OCApi extends Controller
{
  public function showOrder($orderId = '0000000000001')
  {
    $connection = 'sqlsrv_003'; // O dinámico según lógica

    $order = OCModel::getAllOrdersWithProducts($connection);

    return response()->json($order);
  }

  public function getAllCompaniesOCs()
  {
    try {
      // Obtener compañía solicitada y validar input
      $requestedCompany = request()->input('company', null);
      $companies = [$requestedCompany];

      // Mapa estático de códigos a nombres de compañías
      $companyNames = $this->getCompanyNamesMap();

      $dateRange = request()->input('date', null);

      // Parámetros para paginación y filtros
      $page = max(1, (int) request()->input('page', 1));
      $itemsPerPage = max(1, (int) request()->input('itemsPerPage', 10));
      $status = request()->input('status', '00');
      $searchQuery = request()->input('q', null);

      $allOCs = [];
      $total = 0;

      foreach ($companies as $company) {
        $query = $this->buildOCUnifiedQuery($company, $status, $searchQuery, $dateRange);

        $companyTotal = $query->count();
        $total += $companyTotal;

        $ocs = $query->skip(($page - 1) * $itemsPerPage)
          ->take($itemsPerPage)
          ->get();

        $allOCs = array_merge($allOCs, $this->mapUnifiedOCs($ocs, $company, $companyNames));
      }

      // Responder JSON con resultados y total
      return response()->json([
        'ocs' => $allOCs,
        'total' => $total,
      ]);
    } catch (\Exception $e) {

      Log::error('Error al cargar órdenes de compra', [
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'company' => request()->input('company', 'desconocida'),
        'status' => request()->input('status', '00'),
        'searchQuery' => request()->input('q', null),
      ]);
      // Manejo de error con detalles
      return response()->json([
        'error' => 'Error al cargar órdenes',
        'details' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Retorna el mapa estático código => nombre compañía
   */
  private function getCompanyNamesMap(): array
  {
    return [
      '003' => 'LA GRANJA VILLA Y SU MUNDO MAGICO S.A. - GRAN VILLA S.A.',
      '004' => 'EQEQO S.A.C.',
      '005' => 'CHAXRA S.A.C.',
      '006' => 'GIVA S.A.C.',
      '007' => 'SAMI TASK S.A.C.',
      '008' => 'SYVEC S.A.C',
      '009' => 'INVERSIONES TURISTICAS PERUANAS S.A.C',
      '010' => 'YAKU PARK S.A.C.',
      '011' => 'DREAMS COMPANY PERU S.A.C.',
    ];
  }

  /**
   * Construye la query base para obtener órdenes de compra según filtros
   */
  private function buildOCUnifiedQuery(
    string $company,
    string $status,
    ?string $searchQuery,
    ?string $dateRange = null
  ) {
    $connection = 'sqlsrv_' . $company;

    $ocQuery = DB::connection($connection)->table('COMOVC')
      ->selectRaw("'COMPRA' AS origen, OC_CNUMORD, OC_CSITORD, TipoDocumento, OC_DFECENT");

    $ocsQuery = DB::connection($connection)->table('COMOVC_S')
      ->selectRaw("'SERVICIO' AS origen, OC_CNUMORD, OC_CSITORD, TipoDocumento, OC_DFECENT");

    // Filtros comunes
    $applyFilters = function ($query) use ($status, $searchQuery, $dateRange) {
      if ($status !== 'all') {
        $query->where('OC_CSITORD', $status);
      }

      if ($searchQuery !== null) {
        $query->where(function ($q) use ($searchQuery) {
          $q->where('OC_CNUMORD', 'like', '%' . $searchQuery . '%')
            ->orWhere('TipoDocumento', 'like', '%' . $searchQuery . '%');
        });
      }

      if ($dateRange) {
        if (strpos($dateRange, ' a ') !== false) {
          [$startDate, $endDate] = explode(' a ', $dateRange);
          $startDate = Carbon::parse(trim($startDate))->startOfDay();
          $endDate = Carbon::parse(trim($endDate))->endOfDay();
          if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
          }
          $query->whereBetween('OC_DFECENT', [$startDate, $endDate]);
        } else {
          $query->whereDate('OC_DFECENT', Carbon::parse(trim($dateRange)));
        }
      }
    };

    $applyFilters($ocQuery);
    $applyFilters($ocsQuery);

    // Unión de ambas consultas
    $unifiedQuery = $ocQuery->unionAll($ocsQuery);

    // Convertir a queryBuilder
    return DB::connection($connection)->table(DB::raw("({$unifiedQuery->toSql()}) as unified"))
      ->mergeBindings($ocQuery) // Necesario para que funcionen los bindings
      ->orderByDesc('OC_DFECENT');
  }


  /**
   * Mapea resultados de OC a arreglo con formato requerido
   */
  private function mapUnifiedOCs($ocs, string $company, array $companyNames): array
  {
    $mapped = [];

    foreach ($ocs as $oc) {
      $cleanCode = preg_replace('/[^0-9]/', '', $oc->OC_CNUMORD);
      $code = $cleanCode !== '' ? (int)$cleanCode : 0;

      $mapped[] = [
        'company' => $company,
        'company_name' => $companyNames[$company] ?? 'Desconocida',
        'module' => $oc->origen === 'COMPRA' ? 'COMPRAS' : 'SERVICIOS',
        'type' => $oc->TipoDocumento ?? 'N/A',
        'code' => $oc->OC_CNUMORD,
        'status' => $oc->OC_CSITORD,
        'date' => $oc->OC_DFECENT,
        'issue' => ($oc->origen === 'COMPRA' ? 'Orden de Compra' : 'Orden de Servicio') . ' - Nro. ' . $code,
      ];
    }

    return $mapped;
  }
}
