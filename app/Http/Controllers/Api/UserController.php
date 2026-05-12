<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\CompanyModel;
use App\Models\CompanyUserPivot;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected function normalizeAreaPermissions(Request $request, ?string $cargo, ?string $companyIds): ?array
    {
        if (strtoupper(trim((string) $cargo)) !== 'JEFE DE AREA') {
            return null;
        }

        $rawPermissions = $request->input('area_permissions', []);

        if (!is_array($rawPermissions)) {
            return [];
        }

        $selectedCompanies = $companyIds
            ? array_filter(array_map('trim', explode(',', $companyIds)))
            : [];

        $permissions = [];

        foreach ($selectedCompanies as $companyId) {
            $areas = $rawPermissions[$companyId] ?? [];

            $areas = array_values(array_unique(array_filter(
                array_map('trim', (array) $areas),
                fn ($areaId) => $areaId !== ''
            )));

            if (!empty($areas)) {
                $permissions[$companyId] = $areas;
            }
        }

        return $permissions;
    }

    public function getUsers(Request $request)
    {
        $query = User::query();

        // 🔎 Filtro de búsqueda
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q2) use ($search) {
                $q2->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('cargo', 'like', "%$search%");
            });
        }

        // 📌 Ordenamiento
        $sortBy   = $request->input('sortBy', 'name');
        $orderBy  = $request->input('orderBy', 'asc');
        $allowed  = ['name', 'email', 'cargo'];

        if (in_array($sortBy, $allowed)) {
            $query->orderBy($sortBy, $orderBy === 'desc' ? 'desc' : 'asc');
        }

        // 📄 Paginación
        $perPage = max((int) $request->input('itemsPerPage', 10), 1);
        $users   = $query->paginate($perPage);

        return response()->json([
            'users' => UserResource::collection($users->items()),
            'totalUsers' => $users->total(),
            'per_page' => $users->perPage(),
            'current_page' => $users->currentPage(),
        ]);
    }

    public function store(Request $request)
    {
        $companyIds = !empty($request->company)
            ? implode(',', array_map('trim', $request->company))
            : null;

        $areaPermissions = $this->normalizeAreaPermissions($request, $request->cargo, $companyIds);

        $user = User::create([
            'name'             => $request->fullName,
            'cargo'            => $request->cargo,
            'email'            => $request->email,
            'status'           => $request->status,
            'password'         => $request->password,
            'company_ids'      => $companyIds,
            'area_permissions' => $areaPermissions,
        ]);

        return response()->json([
            'message' => 'Usuario creado con éxito',
            'data'    => $user,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $companyIds = !empty($request->company)
            ? implode(',', array_map('trim', $request->company))
            : null;

        $areaPermissions = $this->normalizeAreaPermissions($request, $request->cargo, $companyIds);

        $data = [
            'name'             => $request->fullName,
            'cargo'            => $request->cargo,
            'email'            => $request->email,
            'status'           => $request->status,
            'company_ids'      => $companyIds,
            'company_default'  => $request->company_default,
            'area_permissions' => $areaPermissions,
        ];

        // Solo actualiza contraseña si se envió
        if (!empty($request->password)) {
            $data['password'] = $request->password;
        }

        $user->fill($data)->save();

        return response()->json([
            'message' => 'Usuario actualizado con éxito',
            'data'    => $user,
        ], 200);
    }

    public function updateProfile(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $data = [
            'name'             => $request->fullName,
            'cargo'            => $request->cargo,
            'email'            => $request->email,
            'company_default'  => $request->company,
        ];

        if (!empty($request->password)) {
            $data['password'] = $request->password;
        }

        $user->fill($data)->save();

        return response()->json([
            'message' => 'Perfil actualizado con éxito',
            'data'    => $user,
        ]);
    }


    public function userCompanies()
    {
        $user = auth()->user();

        $companyIds = explode(',', $user->company_ids);

        $companies = CompanyModel::whereIn('EMP_CODIGO', $companyIds)
            ->get()
            ->map(function ($company) {
                return [
                    'id' => $company->EMP_CODIGO,
                    'name' => $company->EMP_RAZON_NOMBRE,
                ];
            });

        return response()->json($companies);
    }

    public function getIdCompanyUser($userId, $companyId)
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        $pivot = $user->companiesPivot()->where('company_id', $companyId)->first();

        if (!$pivot) {
            return response()->json([
                'user_code' => '',
                'approval_email' => '',
            ]);
        }

        return response()->json([
            'user_code' => $pivot->user_code ?? '',
            'approval_email' => $pivot->approval_email ?? '',
        ]);
    }

    public function addCompanyUser(Request $request)
    {
        $pivot = CompanyUserPivot::updateOrCreate([
            'user_id' => $request->user_id,
            'company_id' => $request->company_id,
        ], [
            'user_code' => $request->user_code,
            'approval_email' => $request->approval_email,
        ]);

        return response()->json([
            'message' => 'Empresa asignada al usuario correctamente',
            'data' => $pivot,
        ]);
    }
}
