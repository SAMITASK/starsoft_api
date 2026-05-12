<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $connection = 'auth_db';
    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'cargo',
        'status',
        'company_ids',
        'company_default',
        'area_permissions',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'area_permissions' => 'array',
    ];

    public function companiesPivot()
    {
        return $this->hasMany(CompanyUserPivot::class, 'user_id');
    }

    public function getCompanyIds(): array
    {
        return $this->company_ids
            ? array_filter(array_map('trim', explode(',', $this->company_ids)))
            : [];
    }

    public function getAreaPermissions(): array
    {
        return $this->area_permissions ?? [];
    }

    public function getAllowedAreasForCompany(string $companyId): array
    {
        $permissions = $this->getAreaPermissions();

        if (empty($permissions[$companyId])) {
            return [];
        }

        return array_filter(array_map('trim', (array) $permissions[$companyId]));
    }
}
