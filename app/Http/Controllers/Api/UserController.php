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

        $user = User::create([
            'name'        => $request->fullName,
            'cargo'       => $request->cargo,
            'email'       => $request->email,
            'status'      => $request->status,
            'password'    => $request->password,
            'company_ids' => $companyIds,
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

        $data = [
            'name'        => $request->fullName,
            'cargo'       => $request->cargo,
            'email'       => $request->email,
            'status'      => $request->status,
            'company_ids' => $companyIds,
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
            return response()->json('');
        }

        return response()->json($pivot->user_code);
    }

    public function addCompanyUser(Request $request)
    {

        $pivot = CompanyUserPivot::create([
            'user_id' => $request->user_id,
            'company_id' => $request->company_id,
            'user_code' => $request->user_code,
        ]);

        return response()->json([
            'message' => 'Empresa asignada al usuario correctamente',
            'data' => $pivot,
        ]);
    }
}
