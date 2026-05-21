<?php

namespace App\Services;

use App\Models\CompanyUserPivot;
use Illuminate\Support\Facades\Cache;

class UserPermissionService
{
    private const AREA_MANAGER_ROLE = 'JEFE DE AREA';

    private const UNRESTRICTED_ROLES = [
        'GERENTE',
        'ADMINISTRADOR',
    ];

    public function getResponsibleUser(string $company, ?string $requestedStaff = null): ?string
    {
        return $this->resolveResponsibleUser($company, $requestedStaff);
    }

    public function resolveResponsibleUser(string $company, ?string $requestedStaff = null): ?string
    {
        if (!auth()->check()) {
            return $requestedStaff;
        }

        $user = auth()->user();

        if ($this->hasUnrestrictedAccess($user)) {
            return $requestedStaff;
        }

        return $this->getUserCode($user->id, $this->normalizeCompanyCode($company));
    }

    public function hasUnrestrictedAccess($user = null): bool
    {
        $user ??= auth()->user();
        $role = strtoupper($user->cargo ?? '');

        return in_array($role, self::UNRESTRICTED_ROLES);
    }

    public function isAreaManager($user = null): bool
    {
        $user ??= auth()->user();

        return strtoupper(trim((string) ($user?->cargo ?? ''))) === self::AREA_MANAGER_ROLE;
    }

    public function resolveAuthorizedCompany(?string $requestedCompany): string
    {
        $normalizedRequestedCompany = $this->normalizeCompanyCode($requestedCompany);

        if (!auth()->check()) {
            return $normalizedRequestedCompany ?: '003';
        }

        $user = auth()->user();

        if (!method_exists($user, 'getCompanyIds')) {
            return $normalizedRequestedCompany ?: '003';
        }

        $allowedCompanies = array_values(array_filter(array_map(
            fn ($companyId) => $this->normalizeCompanyCode($companyId),
            $user->getCompanyIds(),
        )));

        if (empty($allowedCompanies)) {
            return $normalizedRequestedCompany ?: '003';
        }

        if ($normalizedRequestedCompany !== '' && in_array($normalizedRequestedCompany, $allowedCompanies, true)) {
            return $normalizedRequestedCompany;
        }

        return $allowedCompanies[0];
    }

    public function getAllowedAreasForCompany(string $company, $user = null): array
    {
        $user ??= auth()->user();

        if (!$user || !method_exists($user, 'getAllowedAreasForCompany')) {
            return [];
        }

        return array_values(array_filter(array_map(
            'trim',
            (array) $user->getAllowedAreasForCompany($this->normalizeCompanyCode($company)),
        )));
    }

    public function resolveAreaFilter(string $company, ?string $requestedArea = null): ?array
    {
        if (!auth()->check()) {
            return $this->buildRequestedAreaFilter($requestedArea);
        }

        $user = auth()->user();

        if (!$this->isAreaManager($user)) {
            return $this->buildRequestedAreaFilter($requestedArea);
        }

        $allowedAreas = $this->getAllowedAreasForCompany($company, $user);

        if (empty($allowedAreas)) {
            return [];
        }

        if (!filled($requestedArea)) {
            return $allowedAreas;
        }

        $normalizedRequestedArea = $this->normalizeAreaCode($requestedArea);
        $matchedArea = collect($allowedAreas)->first(
            fn ($areaCode) => $this->normalizeAreaCode($areaCode) === $normalizedRequestedArea,
        );

        return $matchedArea ? [$matchedArea] : [];
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

    private function buildRequestedAreaFilter(?string $requestedArea): ?array
    {
        if (!filled($requestedArea)) {
            return null;
        }

        return [trim((string) $requestedArea)];
    }

    private function normalizeCompanyCode(?string $company): string
    {
        $normalizedCompany = trim((string) $company);

        if ($normalizedCompany === '') {
            return '';
        }

        return str_pad($normalizedCompany, 3, '0', STR_PAD_LEFT);
    }

    private function normalizeAreaCode(?string $areaCode): string
    {
        $normalizedAreaCode = trim((string) $areaCode);

        if ($normalizedAreaCode !== '' && preg_match('/^\d+$/', $normalizedAreaCode)) {
            return ltrim($normalizedAreaCode, '0') ?: '0';
        }

        return strtoupper($normalizedAreaCode);
    }
}
