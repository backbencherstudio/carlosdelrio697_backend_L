<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Throwable;

class SettingsController extends Controller
{
    public function updateSettings(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'name' => ['required','string','max:255'],
                'last_name' => ['nullable','string','max:255'],
                'email' => ['required','email','max:255','unique:users,email,' . $user->id],
            ]);

            $user->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully',
            ], 200);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
