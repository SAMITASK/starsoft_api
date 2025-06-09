<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OCModel;
use Illuminate\Http\Request;

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
      $requestedCompany = request()->input('company', null);
      $companies = [];

      $companies = [$requestedCompany];

      $companyNames = [
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

      $allOCs = [];
      $total = 0;
      $page = max(1, (int) request()->input('page', 1));
      $itemsPerPage = max(1, (int) request()->input('itemsPerPage', 10));
      $status = request()->input('status', '00');
      $searchQuery = request()->input('q', null);

      foreach ($companies as $company) {
        $connection = 'sqlsrv_' . $company;
        $query = (new OCModel())->setConnection($connection)->newQuery();

        if ($status !== 'all') {
          $query->where('OC_CSITORD', $status);
        }

        if ($searchQuery !== null) {
          $query->where('OC_CNUMORD', 'like', '%' . $searchQuery . '%');
        }

        // Conteo total para esta empresa con filtro aplicado
        $companyTotal = $query->count();
        $total += $companyTotal;

        $ocs = $query->skip(($page - 1) * $itemsPerPage)
          ->take($itemsPerPage)
          ->get();

        $num = ($page - 1) * $itemsPerPage;

        foreach ($ocs as $oc) {
          $num++;
          $cleanCode = preg_replace('/[^0-9]/', '', $oc->OC_CNUMORD);
          $code = $cleanCode !== '' ? (int)$cleanCode : 0;

          $allOCs[] = [
            'id' => $num,
            'company' => $company,
            'company_name' => $companyNames[$company] ?? 'Desconocida',
            'module' => 'COMPRAS',
            'type' => 'OC',
            'code' => $oc->OC_CNUMORD,
            'issue' => 'Aprobación de Orden de Compra - Nro. ' . $code,
            'status' => $oc->OC_CSITORD
          ];
        }
      }

      return response()->json([
        'ocs' => $allOCs,
        'total' => $total,
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'error' => 'Error al cargar órdenes',
        'details' => $e->getMessage()
      ], 500);
    }
  }
}
