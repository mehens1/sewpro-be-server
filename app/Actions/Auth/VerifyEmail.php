<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class VerifyEmail
{
    use AsAction;
    use ApiResponse;

    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:users,id',
            'hash' => 'required|string',
        ];
    }

    public function handle(array $params)
    {
        $userId = $params['id'];
        $hash = $params['hash'];

        try {
            $user = User::find($userId);

            if (!$user) {
                return $this->errorResponse('User not found', 404, [
                    'error' => 'user_not_found'
                ]);
            }

            // Verify hash matches user's email
            if (!hash_equals($hash, sha1($user->getEmailForVerification()))) {
                return $this->errorResponse('Invalid verification link', 403, [
                    'error' => 'invalid_verification_link'
                ]);
            }

            if ($user->hasVerifiedEmail()) {
                return $this->successResponse([], 'Email already verified');
            }

            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
            }

            return $this->successResponse([], 'Email verified successfully!');

        } catch (\Exception $e) {
            Log::error("Email verification failed", [
                "type" => "email_verification_failed",
                "server_error" => true,
                "exception" => $e->getMessage(),
                "user_id" => $userId
            ]);

            return $this->errorResponse('An error occurred during email verification', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function asController(ActionRequest $request)
    {
        // Get route parameters for email verification
        $params = [
            'id' => $request->route('id'),
            'hash' => $request->route('hash'),
        ];

        return $this->handle($params);
    }
}