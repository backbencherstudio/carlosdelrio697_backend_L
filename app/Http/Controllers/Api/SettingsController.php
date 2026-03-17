<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Throwable;
use App\Models\Settings;
use App\Http\Requests\Settings\UpdatePlatformSettingsRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    public function getSettings(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $settings = Settings::where('user_id', $user->id)->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'user_settings' => [
                        'name' => $user->name,
                        'last_name' => $user->last_name,
                        'email' => $user->email,
                    ],
                    'platform_settings' => [
                        'company_name' => $settings->company_name ?? null,
                        'support_mail' => $settings->support_mail ?? null,
                    ],
                    'notification_settings' => [
                        'email_on_new_orders' => $settings->email_on_new_orders ?? false,
                        'email_on_compliance_flags' => $settings->email_on_compliance_flags ?? false,
                        'email_on_risk_allert' => $settings->email_on_risk_allert ?? false,
                    ]
                ]
            ], 200);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

   public function updateSettings(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
                'profile_image' => ['sometimes', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ]);

            if ($request->hasFile('profile_image')) {

                if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                    Storage::disk('public')->delete($user->profile_image);
                }

                $filename = Str::uuid() . '.' . $request->file('profile_image')->extension();

                $path = $request->file('profile_image')->storeAs(
                    'uploads/admin',
                    $filename,
                    'public'
                );

                $validated['profile_image'] = $path;
            }

            $user->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully',
                'data' => [
                    'name' => $user->name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'profile_image' => $user->profile_image
                        ? asset('storage/' . $user->profile_image)
                        : null
                ]
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
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateNotificationSettings(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'email_on_new_orders' => ['sometimes', 'boolean'],
                'email_on_compliance_flags' => ['sometimes', 'boolean'],
                'email_on_risk_allert' => ['sometimes', 'boolean'],
            ]);

            if (empty($validated)) {
                return response()->json([
                    'success' => false,
                    'message' => 'At least one notification setting must be provided.'
                ], 422);
            }

            $settings = Settings::updateOrCreate(
                ['user_id' => $user->id],
                array_merge(['user_id' => $user->id], $validated)
            );

            return response()->json([
                'success' => true,
                'message' => 'Notification settings updated successfully.',
            ], 200);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update notification settings.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
