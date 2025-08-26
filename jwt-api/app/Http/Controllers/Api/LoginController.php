<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function logout(Request $request)
    {
        $accessToken = $request->header('Authorization');
        if (!$accessToken) {
            return response()->json(['error' => 'Access token missing'], 401);
        }

        $user = User::where('access_token', $accessToken)->first();
        if (!$user) {
            return response()->json(['error' => 'Invalid access token'], 401);
        }

        $user->access_token = null;
        $user->no_of_accesses = 0;
        $user->save();

        return response()->json(['message' => 'Logged out successfully']);
    }
    
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // Combine input password with user's salt and check hash
        $hashedInput = $validated['password'] . $user->salt;
        if (!Hash::check($hashedInput, $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // Generate a simple access token (for demo, use Laravel Passport or Sanctum for production)
        $token = bin2hex(random_bytes(32));

        // Update user's access token
        $user->access_token = $token;
        $user->save();

        return response()->json(['access_token' => $token]);
    }
}