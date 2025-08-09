<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserCheckController extends Controller
{
    //
    public function checkUser(Request $request)
    {
        $email = $request->query('email');
        $exists = User::where('email', $email)->exists();
        return response()->json(['exists' => $exists]);
    }

    public function checkMultiple(Request $request)
    {
        $accessToken = $request->header('Authorization');
        if (!$accessToken) {
            return response()->json(['error' => 'Access token missing'], 401);
        }

        // Optionally, validate the token against the database
        $user = User::where('access_token', $accessToken)->first();
        if (!$user) {
            return response()->json(['error' => 'Invalid access token'], 401);
        }
        
        $validated = $request->validate([
            'emails' => 'required|array',
            'emails.*' => 'required|email'
        ]);

        $found = User::whereIn('email', $validated['emails'])->pluck('email')->toArray();
        $notFound = array_diff($validated['emails'], $found);

        return response()->json(['nonexistent' => array_values($notFound)]);
    }
}
