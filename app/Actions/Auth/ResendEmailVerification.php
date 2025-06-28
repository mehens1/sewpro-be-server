<?php

namespace App\Actions\Auth;

use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ResendEmailVerification
{
    use AsAction;
    use ApiResponse;

    public function rules(): array
    {
        return [
            // No additional validation needed - user must be authenticated
        ];
    }

    public function handle(array $params, $user = null)
    {
        try {
            if (!$user) {
                return $this->errorResponse('User not authenticated', 401, [
                    'error' => 'unauthenticated'
                ]);
            }

            if ($user->hasVerifiedEmail()) {
                return $this->successResponse([], 'Email already verified');
            }

            $user->sendEmailVerificationNotification();

            return $this->successResponse([], 'Verification link sent successfully!');

        } catch (\Exception $e) {
            Log::error("Resend email verification failed", [
                "type" => "resend_verification_failed",
                "server_error" => true,
                "exception" => $e->getMessage(),
                "user_id" => $user?->id
            ]);

            return $this->errorResponse('An error occurred while sending verification email', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function asController(ActionRequest $request)
    {
        return $this->handle($request->all(), $request->user());
    }
}