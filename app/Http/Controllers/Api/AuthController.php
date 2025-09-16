<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:5',
            'remember_me' => 'nullable|boolean',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->errorResponse(
                'Cuenta no encontrada',
                'email',
                'No existe una cuenta con este email.'
            );
        }

        if ($user->status !== 'active') {
            return $this->errorResponse(
                'Cuenta inactiva',
                'email',
                'Su cuenta está inactiva. Contacte al administrador.'
            );
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->errorResponse(
                'Credenciales inválidas',
                'password',
                'La contraseña es incorrecta.'
            );
        }

        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->plainTextToken;

        return response()->json([
            'accessToken' => $token,
            'token_type' => 'Bearer',
            'userData' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status,
            ],
        ]);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Handle user logout
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Sesión cerrada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al cerrar sesión'
            ], 500);
        }
    }

    private function errorResponse(string $message, string $field, string $detail, int $code = 401)
    {
        return response()->json([
            'message' => $message,
            'errors' => [
                $field => [$detail]
            ]
        ], $code);
    }
}
