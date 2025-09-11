<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class DeleteAccountController extends Controller
{
	public function destroy(Request $request)
	{
		$accessToken = $request->header('Authorization');
		if (!$accessToken) {
			return response()->json(['error' => 'Access token missing'], 401);
		}

		$user = User::where('access_token', $accessToken)->first();
		if (!$user) {
			return response()->json(['error' => 'Invalid access token'], 401);
		}

		$user->delete();

		return response()->json(['message' => 'Account deleted successfully.']);
	}
}
