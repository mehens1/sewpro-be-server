<?php

namespace App\Actions\Auth;

use App\Traits\ApiResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\ActionRequest;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\PasswordResetToken;
use App\Mail\ResetPasswordCodeMail;

class ResetPassword
{
    use AsAction, ApiResponse;

    public function rules(): array
    {
        return [
            'email' => 'bail|required|email',
        ];
    }

    public function handle(array $params)
    {
        try {
            $user = User::where('email', $params['email'])->first();

            if (!$user) {
                return $this->errorResponse('This email has not been registered, please register.', 422, [
                    'error' => 'Email does not exist.'
                ]);
            }

            $resetCode = random_int(100000, 999999);

            PasswordResetToken::updateOrCreate(
                ['email' => $user->email],
                [
                    'token' => $resetCode
                ]
            );

            Mail::to($user->email)->send(new ResetPasswordCodeMail($resetCode));

            return $this->successResponse([
                'message' => 'A reset code has been sent to your email address.'
            ]);
        } catch (\Exception $exp) {
            Log::error("Password reset code send failed", [
                "type"         => "password_reset_code_failed",
                "server_error" => true,
                "email"        => $params['email'] ?? null,
                "exception"    => $exp->getMessage()
            ]);

            return $this->errorResponse('Failed to send reset code.', 500, [
                'error' => $exp->getMessage()
            ]);
        }
    }

    public function asController(ActionRequest $request)
    {
        return $this->handle($request->validated());
    }
}
