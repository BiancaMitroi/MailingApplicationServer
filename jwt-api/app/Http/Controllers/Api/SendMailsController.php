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
        \Log::info('Send mail request received', ['data' => $request->all()]);

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
        if ($user->no_of_accesses > 10) {
            $user->access_token = null;
            $user->no_of_accesses = 0;
        }
        $user->save();

        $validated = $request->validate([
            'recipients' => 'required',
            'subject' => 'required|string',
            'message' => 'required|string',
        ]);
        \Log::info('Email validation passed', ['data' => $validated]);

        // Convert recipients to array if it's a string
        $recipients = $validated['recipients'];
        if (is_string($recipients)) {
            $recipients = array_map('trim', explode(',', $recipients));
        }
        // Validate each recipient as email
        foreach ($recipients as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return response()->json(['error' => "Invalid email: $email"], 422);
            }
        }

        // Get attachments as UploadedFile objects (if any)
        $attachments = $request->file('attachments', []);
        if (!is_array($attachments)) {
            $attachments = [$attachments];
        }

        $errors = [];
        foreach ($recipients as $recipient) {
            try {
                Mail::raw($validated['message'], function ($mail) use ($recipient, $validated, $attachments, &$errors) {
                    $mail->to($recipient)
                        ->subject($validated['subject']);
                    foreach ($attachments as $attachment) {
                        if ($attachment) {
                            try {
                                $mail->attach($attachment->getRealPath(), [
                                    'as' => $attachment->getClientOriginalName(),
                                    'mime' => $attachment->getMimeType(),
                                ]);
                            } catch (\Exception $e) {
                                $errors[] = "Attachment error for $recipient: " . $e->getMessage();
                            }
                        }
                    }
                });
            } catch (\Exception $e) {
                $errors[] = "Mail error for $recipient: " . $e->getMessage();
            }
        }

        \Log::info('Emails are prepared to be sent');

        if (!empty($errors)) {
            \Log::info('Emails failed to send', ['errors' => $errors]);
            return response()->json([
                'message' => 'Some emails failed to send.',
                'errors' => $errors
            ], 500);
        }

        return response()->json(['message' => 'Emails sent successfully']);
    }
}
