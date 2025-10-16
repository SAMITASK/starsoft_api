<?php

namespace App\Services;

use App\Models\CompanyUserPivot;
use Illuminate\Support\Facades\Cache;

class UserPermissionService
{
    private const UNRESTRICTED_ROLES = [
        'GERENTE',
        'ADMINISTRADOR',
    ];

    public function getResponsibleUser(string $company): ?string
    {
        if (!auth()->check()) {
            return null;
        }

        $user = auth()->user();

        if ($this->hasUnrestrictedAccess($user)) {
            return null;
        }

        return $this->getUserCode($user->id, $company);
    }

    private function hasUnrestrictedAccess($user): bool
    {
        $role = strtoupper($user->cargo ?? '');
        return in_array($role, self::UNRESTRICTED_ROLES);
    }

    private function getUserCode(int $userId, string $company): ?string
    {
        // Cache por 1 hora
        $cacheKey = "user_code_{$userId}_{$company}";

        return Cache::remember($cacheKey, 3600, function () use ($userId, $company) {
            return CompanyUserPivot::where('user_id', $userId)
                ->where('company_id', $company)
                ->value('user_code');
        });
    }
}
