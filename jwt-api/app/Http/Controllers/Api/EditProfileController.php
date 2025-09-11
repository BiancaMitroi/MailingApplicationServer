<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class EditProfileController extends Controller
{
    public function update(Request $request)
    {
        $accessToken = $request->header('Authorization');
        if (!$accessToken) {
            return response()->json(['error' => 'Access token missing'], 401);
        }

        $user = User::where('access_token', $accessToken)->first();
        if (!$user) {
            return response()->json(['error' => 'Invalid access token'], 401);
        }

        $validated = $request->validate([
            'firstName' => 'required|string|max:255',
            'lastName'  => 'required|string|max:255',
            'email'     => 'required|email|max:255',
            'password'  => 'nullable|string|min:8'
        ]);

        $user->firstname = $validated['firstName'];
        $user->lastname = $validated['lastName'];
        $user->email = $validated['email'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json(['message' => 'Profile updated successfully!']);
    }

    public function show(Request $request)
    {
        $accessToken = $request->header('Authorization');
        if (!$accessToken) {
            return response()->json(['error' => 'Access token missing'], 401);
        }

        $user = User::where('access_token', $accessToken)->first();
        if (!$user) {
            return response()->json(['error' => 'Invalid access token'], 401);
        }

        return response()->json([
            'firstName' => $user->firstName,
            'lastName'  => $user->lastName,
            'email'     => $user->email
        ]);
    }
}