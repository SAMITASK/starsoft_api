<?php

declare(strict_types=1);

use App\Models\Area;
use App\Models\CompanyModel;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$normalizeAreaCode = static function ($areaCode): string {
    $normalizedAreaCode = trim((string) $areaCode);

    if ($normalizedAreaCode !== '' && preg_match('/^\d+$/', $normalizedAreaCode)) {
        return ltrim($normalizedAreaCode, '0') ?: '0';
    }

    return strtoupper($normalizedAreaCode);
};

$companies = CompanyModel::query()
    ->whereNotIn('EMP_CODIGO', ['001', '002'])
    ->orderBy('EMP_CODIGO')
    ->get(['EMP_CODIGO', 'EMP_RAZON_NOMBRE']);

$areaAssignments = [];

$areaManagers = User::query()
    ->whereRaw("UPPER(TRIM(cargo)) = 'JEFE DE AREA'")
    ->orderBy('name')
    ->get(['id', 'name', 'area_permissions']);

foreach ($areaManagers as $manager) {
    foreach ((array) $manager->area_permissions as $companyCode => $areas) {
        $normalizedCompanyCode = str_pad(trim((string) $companyCode), 3, '0', STR_PAD_LEFT);

        foreach ((array) $areas as $areaCode) {
            $normalizedAreaCode = $normalizeAreaCode($areaCode);
            $assignmentKey = "{$normalizedCompanyCode}|{$normalizedAreaCode}";

            $areaAssignments[$assignmentKey] ??= [];
            $areaAssignments[$assignmentKey][] = $manager->name;
        }
    }
}

$rows = [];

foreach ($companies as $company) {
    $companyCode = trim((string) $company->EMP_CODIGO);
    $connection = 'sqlsrv_' . $companyCode;

    try {
        $areas = Area::on($connection)
            ->select('AREA_CODIGO', 'AREA_DESCRIPCION')
            ->orderBy('AREA_DESCRIPCION')
            ->get();
    } catch (\Throwable $exception) {
        $rows[] = [
            'company_code' => $companyCode,
            'company_name' => $company->EMP_RAZON_NOMBRE,
            'area_code' => '',
            'area_name' => 'No se pudo consultar áreas',
            'manager_name' => '',
        ];
        continue;
    }

    foreach ($areas as $area) {
        $normalizedAreaCode = $normalizeAreaCode($area->AREA_CODIGO);
        $assignmentKey = "{$companyCode}|{$normalizedAreaCode}";
        $managerNames = $areaAssignments[$assignmentKey] ?? [];

        $rows[] = [
            'company_code' => $companyCode,
            'company_name' => trim((string) $company->EMP_RAZON_NOMBRE),
            'area_code' => trim((string) $area->AREA_CODIGO),
            'area_name' => trim((string) $area->AREA_DESCRIPCION),
            'manager_name' => implode(', ', array_values(array_unique($managerNames))),
        ];
    }
}

echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
