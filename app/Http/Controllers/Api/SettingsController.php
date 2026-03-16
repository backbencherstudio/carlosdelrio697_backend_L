<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Throwable;
use App\Models\Settings;
use App\Http\Requests\Settings\UpdatePlatformSettingsRequest;

class SettingsController extends Controller
{
    public function updateSettings(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'name' => ['required','string','max:255'],
                'last_name' => ['required','string','max:255'],
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

    public function updatePlatformSettings(UpdatePlatformSettingsRequest $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validated = $request->validated();

            if (empty($validated)) {
                return response()->json([
                    'success' => false,
                    'message' => 'At least one field must be provided.',
                ], 422);
            }

            $settings = Settings::updateOrCreate(
                ['user_id' => $user->id],
                array_merge(['user_id' => $user->id], $validated)
            );

            return response()->json([
                'success' => true,
                'message' => 'Platform settings updated successfully.',
                'data' => $settings
            ], 200);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update platform settings.',
                'error' => config('app.debug') ? $e->getMessage() : ''
            ], 500);
        }
    }
}
