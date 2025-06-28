<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ResetPassword
{
    use AsAction;
    use ApiResponse;

    public function rules(): array
    {
        return [
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];
    }

    public function handle(array $params)
    {
        $token = $params['token'];
        $email = $params['email'];
        $password = $params['password'];

        try {
            $status = Password::reset(
                [
                    'email' => $email,
                    'password' => $password,
                    'password_confirmation' => $params['password_confirmation'],
                    'token' => $token
                ],
                function (User $user, string $password) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                        'remember_token' => Str::random(60),
                    ])->save();

                    event(new PasswordReset($user));
                }
            );

            if ($status !== Password::PASSWORD_RESET) {
                return $this->errorResponse(__($status), 400, [
                    'error' => 'password_reset_failed',
                    'status' => $status
                ]);
            }

            return $this->successResponse([], 'Your password has been reset successfully!');

        } catch (\Exception $e) {
            Log::error("Password reset failed", [
                "type" => "password_reset_failed",
                "server_error" => true,
                "exception" => $e->getMessage(),
                "email" => $email
            ]);

            return $this->errorResponse('An error occurred while resetting your password', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function asController(ActionRequest $request)
    {
        return $this->handle($request->all());
    }
}