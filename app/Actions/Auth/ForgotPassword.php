<?php

namespace App\Actions\Auth;

use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ForgotPassword
{
    use AsAction;
    use ApiResponse;

    public function rules(): array
    {
        return [
            'email' => 'required|email|exists:users,email',
        ];
    }

    public function handle(array $params, string $ipAddress = null)
    {
        $email = $params['email'];
        
        try {
            // Rate limiting check
            if ($ipAddress) {
                $this->checkRateLimit($ipAddress);
            }

            $status = Password::sendResetLink(['email' => $email]);

            if ($status !== Password::RESET_LINK_SENT) {
                return $this->errorResponse(__($status), 400, [
                    'error' => 'reset_link_failed',
                    'status' => $status
                ]);
            }

            return $this->successResponse([], 'We have emailed your password reset link!');

        } catch (\Exception $e) {
            Log::error("Forgot password failed", [
                "type" => "forgot_password_failed",
                "server_error" => true,
                "exception" => $e->getMessage(),
                "email" => $email
            ]);
            
            return $this->errorResponse('An error occurred while processing your request', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function asController(ActionRequest $request)
    {
        return $this->handle($request->all(), $request->ip());
    }

    private function checkRateLimit(string $ipAddress): void
    {
        $key = 'forgot-password:' . $ipAddress;
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw new \Exception("Too many attempts. Please try again in {$seconds} seconds.");
        }

        RateLimiter::hit($key, 3600); // 1 hour
    }
}