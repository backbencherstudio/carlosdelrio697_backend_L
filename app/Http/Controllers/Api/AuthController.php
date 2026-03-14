<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Log::info($request);
        $credentials = $request->only('email', 'password');

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $user = Auth::guard('api')->user();

        if ($user->jwt_token) {
            try {
                JWTAuth::setToken($user->jwt_token)->invalidate();
            } catch (\Exception $e) {}
        }

        $user->update(['jwt_token' => $token]);

        return response()->json([
            'token' => $token,
            'user' => $user,
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }

    public function logout()
    {
        $user = Auth::guard('api')->user();

        if ($user && $user->jwt_token) {
            JWTAuth::setToken($user->jwt_token)->invalidate();
            $user->update(['jwt_token' => null]);
        }

        return response()->json(['message' => 'Logged out successfully']);
    }
}
