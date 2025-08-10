<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class SendMailsController extends Controller
{
    public function send(Request $request)
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

        $user->no_of_accesses = ($user->no_of_accesses ?? 0) + 1;
        $user->save();

        $validated = $request->validate([
            'recipients' => 'required|array',
            'recipients.*' => 'required|email',
            'subject' => 'required|string',
            'message' => 'required|string',
        ]);

        foreach ($validated['recipients'] as $recipient) {
            Mail::raw($validated['message'], function ($mail) use ($recipient, $validated) {
                $mail->to($recipient)
                    ->subject($validated['subject']);
            });
        }

        return response()->json(['message' => 'Emails sent successfully']);
    }
}
