<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        $request->session()->regenerate();

        $request->user()->audit('logged_in');

        return response()->json([
            'message' => 'Logged in',
            'user' => $request->user(),
        ]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            // expects `password_confirmation` field from the frontend
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // log them in right after registration
        Auth::login($user);

        $user->audit('registered');

        $request->session()->regenerate();

        return response()->json([
            'message' => 'Registered and logged in',
            'user' => $user,
        ], 201);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $user->audit('logged_out');
        }
        Auth::guard('web')->logout();

        $request->session()->forget('current_company_id');

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return ['message' => 'Logged out'];

    }
}
