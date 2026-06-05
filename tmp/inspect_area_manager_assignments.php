<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Contracts\Console\Kernel;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$users = User::query()
    ->whereRaw("UPPER(TRIM(cargo)) = 'JEFE DE AREA'")
    ->orderBy('name')
    ->get(['id', 'name', 'email', 'company_ids', 'area_permissions']);

echo json_encode(
    $users->map(fn ($user) => [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'company_ids' => $user->company_ids,
        'area_permissions' => $user->area_permissions,
    ]),
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
);
