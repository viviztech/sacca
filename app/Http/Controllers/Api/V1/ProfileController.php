<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load('branch');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'whatsapp_number' => $user->whatsapp_number,
                'employee_id' => $user->employee_id,
                'role' => $user->role->value,
                'role_label' => $user->role->label(),
                'branch_id' => $user->branch_id,
                'branch_name' => $user->branch?->name,
                'avatar' => $user->avatar,
                'is_active' => $user->is_active,
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'whatsapp_number' => ['sometimes', 'nullable', 'string', 'max:20'],
        ]);

        $request->user()->update($request->only(['name', 'phone', 'whatsapp_number']));

        return response()->json(['success' => true, 'message' => 'Profile updated.']);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        if (! Hash::check($request->current_password, $request->user()->password)) {
            return response()->json(['success' => false, 'message' => 'Current password is incorrect.'], 422);
        }

        $request->user()->update(['password' => Hash::make($request->password)]);

        return response()->json(['success' => true, 'message' => 'Password updated.']);
    }

    public function updateAvatar(Request $request): JsonResponse
    {
        $request->validate(['avatar' => ['required', 'image', 'max:2048']]);

        $path = $request->file('avatar')->store('avatars', 'public');

        $request->user()->update(['avatar' => $path]);

        return response()->json([
            'success' => true,
            'data' => ['avatar' => $path],
            'message' => 'Avatar updated.',
        ]);
    }
}
