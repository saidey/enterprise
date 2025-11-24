<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Get current authenticated user profile.
     */
    public function show(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'data' => $user,
        ]);
    }

    /**
     * Update profile (not password).
     */
    public function update(Request $request)
    {
        $user = $request->user();

        // Capture original values before we change anything
        $original = $user->getOriginal();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email,'.$user->id.',id',
            ],
            'phone' => ['nullable', 'string', 'max:50'],

            'national_id' => [
                'nullable',
                'string',
                'max:20',
                'unique:users,national_id,'.$user->id.',id',
            ],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:20'],

            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'island' => ['nullable', 'string', 'max:255'],
            'atoll' => ['nullable', 'string', 'max:255'],

            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:50'],
        ]);

        // Apply changes
        $user->fill($data);

        // Which fields actually changed?
        $dirty = $user->getDirty(); // ['name' => 'New', 'phone' => '...']

        // Build old/new arrays ONLY for changed fields
        $auditOld = [];
        $auditNew = [];

        foreach ($dirty as $field => $newValue) {
            $auditOld[$field] = $original[$field] ?? null;
            $auditNew[$field] = $newValue;
        }

        $user->save();

        // Custom audit entry with real before/after values
        if (! empty($auditNew) && method_exists($user, 'writeAuditLog')) {
            $user->writeAuditLog('profile_updated', $auditOld, $auditNew);
        }

        return response()->json([
            'message' => 'Profile updated successfully.',
            'data' => $user->fresh(),
        ]);
    }

    /**
     * Change password.
     */
    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($data['current_password'], $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $user->password = Hash::make($data['password']);
        $user->save();

        // Audit password change (no sensitive data)
        if (method_exists($user, 'writeAuditLog')) {
            $user->writeAuditLog('password_changed', null, [
                'changed' => true,
            ]);
        }

        return response()->json([
            'message' => 'Password updated successfully.',
        ]);
    }
}
