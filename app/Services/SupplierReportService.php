<?php

namespace App\Services;

use App\Models\ProductModel;
use App\Utils\DateRangeParser;
use App\Services\UserPermissionService;
use Illuminate\Support\Collection;

class SupplierReportService
{
  public function __construct(
    private DateRangeParser $dateParser,
    private UserPermissionService $permissionService
  ) {}

  public function getSupplierProducts(
    string $company,
    string $area,
    string $dateRange,
    ?string $type
  ): Collection {
    $conexion = "sqlsrv_{$company}";

    // Parsear fechas
    [$startDate, $endDate] = $this->dateParser->parse($dateRange);

    // Verificar permisos y obtener responsable
    $responsible = $this->permissionService->getResponsibleUser($company);

    // Consultar datos
    $results = ProductModel::getOrdersByAreaWithProducts(
      $conexion,
      $startDate,
      $endDate,
      $area,
      $responsible,
      $type
    );

    return collect($results);
  }

  public function formatResponse(Collection $suppliers, array $filters): array
  {
    return [
      'suppliers' => $suppliers->toArray(),
      'total' => $suppliers->count(),
    ];
  }
}
