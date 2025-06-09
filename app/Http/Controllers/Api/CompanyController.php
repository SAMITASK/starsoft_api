<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanyModel;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function show()
    {
        $companies = CompanyModel::whereNotIn('EMP_CODIGO', ['001', '002'])
            ->get()
            ->map(function ($company) {
                return [
                    'id' => $company->EMP_RAZON_NOMBRE,
                    'name' => $company->EMP_CODIGO,
                ];
            });

        return response()->json($companies);
    }
}
