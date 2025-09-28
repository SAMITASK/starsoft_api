<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
{
    // Lista global de empresas desde el provider
    $companies = collect(app('companies'));

    // Convertimos el string "001,002,003" en un array
    $userCompanyIds = $this->company_ids
        ? array_map('trim', explode(',', $this->company_ids))
        : [];

    // Filtramos las empresas que le pertenecen al usuario
    $userCompanies = $companies->whereIn('EMP_CODIGO', $userCompanyIds);

    return [
        'id'          => $this->id,
        'name'        => $this->name,
        'email'       => $this->email,
        'role'        => $this->cargo,
        // 🔹 Ahora devolvemos como ARRAY
        'companies'   => $userCompanies->map(fn($c) => [
            'id'   => $c['EMP_CODIGO'],
            'name' => Str::limit($c['EMP_RAZON_NOMBRE'], 30),
        ])->values(),
        'company_ids' => $userCompanyIds,
        'status'      => $this->status,
    ];
}
}
