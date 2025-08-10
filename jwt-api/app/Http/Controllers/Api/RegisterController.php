<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'firstName' => 'required|string|max:255',
            'lastName'  => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:6',
        ]);

        $salt = bin2hex(random_bytes(16));
        $hashedPassword = Hash::make($validated['password'] . $salt);

        $user = User::create([
            'firstname'     => $validated['firstName'],
            'lastname'      => $validated['lastName'],
            'email'    => $validated['email'],
            'password' => $hashedPassword,
            'salt'     => $salt,
            'access_token' => '',
            'no_of_accesses' => 0, // Initialize no_of_accesses
        ]);

        return response()->json(['user' => $user], 201);
    }
}