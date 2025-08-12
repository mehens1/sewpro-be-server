<?php

namespace App\Actions\Auth;

use App\Traits\ApiResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\ActionRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\PasswordResetToken;

class VerifyCode
{
    use AsAction, ApiResponse;

    public function rules(): array
    {
        return [
            'email' => 'bail|required|email',
            'token' => 'bail|required|string',
        ];
    }

    public function handle(array $params)
    {
        DB::beginTransaction();

        try {
            $user = User::where('email', $params['email'])->first();
            if (!$user) {
                return $this->errorResponse('This email is not registered.', 422, [
                    'error' => 'Email does not exist.'
                ]);
            }

            $resetRecord = PasswordResetToken::where('email', $params['email'])->first();
            if (!$resetRecord) {
                return $this->errorResponse('No reset request found for this email.', 404);
            }

            if ($resetRecord->token !== $params['token']) {
                return $this->errorResponse('Invalid reset code.', 422);
            }

            $tempToken = bin2hex(random_bytes(32));

            $user->update([
                'remember_token' => $tempToken
            ]);

            $resetRecord->delete();

            DB::commit();

            return $this->successResponse([
                'message' => 'Code verified successfully. Email has been verified.',
                'token'   => $tempToken
            ]);
        } catch (\Exception $exp) {
            DB::rollBack();

            Log::error("Password reset code verification failed", [
                "type"         => "password_reset_code_verification_failed",
                "server_error" => true,
                "email"        => $params['email'] ?? null,
                "exception"    => $exp->getMessage()
            ]);

            return $this->errorResponse('Failed to verify reset code.', 500, [
                'error' => $exp->getMessage()
            ]);
        }
    }

    public function asController(ActionRequest $request)
    {
        return $this->handle($request->validated());
    }
}
