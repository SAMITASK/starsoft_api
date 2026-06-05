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
    ->get(['EMP_CODIGO', 'EMP_RAZON_NOMBRE'])
    ->keyBy('EMP_CODIGO');

$areaCatalogByCompany = [];

$users = User::query()
    ->whereRaw("UPPER(TRIM(cargo)) = 'JEFE DE AREA'")
    ->orderBy('name')
    ->get();

$companiesSummary = [];

foreach ($users as $user) {
    $permissions = $user->getAreaPermissions();

    if (empty($permissions)) {
        continue;
    }

    foreach ($permissions as $companyCode => $areas) {
        $normalizedCompanyCode = str_pad(trim((string) $companyCode), 3, '0', STR_PAD_LEFT);

        if (!isset($companiesSummary[$normalizedCompanyCode])) {
            $companyName = $companies[$normalizedCompanyCode]->EMP_RAZON_NOMBRE ?? "Empresa {$normalizedCompanyCode}";

            $companiesSummary[$normalizedCompanyCode] = [
                'company_code' => $normalizedCompanyCode,
                'company_name' => $companyName,
                'managers' => [],
            ];
        }

        if (!isset($areaCatalogByCompany[$normalizedCompanyCode])) {
            $connection = 'sqlsrv_' . $normalizedCompanyCode;

            try {
                $areaCatalogByCompany[$normalizedCompanyCode] = Area::on($connection)
                    ->select('AREA_CODIGO', 'AREA_DESCRIPCION')
                    ->orderBy('AREA_DESCRIPCION')
                    ->get()
                    ->mapWithKeys(fn ($area) => [
                        $normalizeAreaCode($area->AREA_CODIGO) => [
                            'code' => trim((string) $area->AREA_CODIGO),
                            'name' => trim((string) $area->AREA_DESCRIPCION),
                        ],
                    ])
                    ->all();
            } catch (\Throwable $exception) {
                $areaCatalogByCompany[$normalizedCompanyCode] = [];
            }
        }

        $managerAreas = [];

        foreach ((array) $areas as $areaCode) {
            $normalizedAreaCode = $normalizeAreaCode($areaCode);
            $areaInfo = $areaCatalogByCompany[$normalizedCompanyCode][$normalizedAreaCode] ?? null;

            $managerAreas[] = [
                'code' => trim((string) $areaCode),
                'name' => $areaInfo['name'] ?? trim((string) $areaCode),
            ];
        }

        usort($managerAreas, fn ($left, $right) => strcmp($left['name'], $right['name']));

        $companiesSummary[$normalizedCompanyCode]['managers'][] = [
            'manager_name' => $user->name,
            'email' => $user->email,
            'areas' => $managerAreas,
        ];
    }
}

ksort($companiesSummary);

foreach ($companiesSummary as &$companySummary) {
    usort($companySummary['managers'], fn ($left, $right) => strcmp($left['manager_name'], $right['manager_name']));
}

echo json_encode(array_values($companiesSummary), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
