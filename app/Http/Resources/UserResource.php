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
            'id'        => $this->id,
            'name'      => $this->name,
            'email'     => $this->email,
            'role'     => $this->cargo,
            'companies' => $userCompanies->pluck('EMP_RAZON_NOMBRE')
                ->map(fn($name) => Str::limit($name, 22)) // corta a 20 caracteres
                ->implode(', '),
            'company_ids' => $userCompanyIds,
            'status'    => $this->status,
        ];
    }
}
