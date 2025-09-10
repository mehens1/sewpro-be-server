<?php

namespace App\Actions\Auth;

use App\Mail\ThankYouForVerificationMail;
use App\Traits\ApiResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\ActionRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\PasswordResetToken;
use Illuminate\Support\Facades\Mail;

class VerifyCode
{
    use AsAction, ApiResponse;

    public function rules(): array
    {
        return [
            'email' => 'bail|required|email',
            'token' => 'bail|required|string',
            'verifyEmail' => 'sometimes|boolean',
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
                return $this->errorResponse('Invalid verification code.', 422);
            }

            $message = '';
            $responseData = [];

            if (!empty($params['verifyEmail']) && $params['verifyEmail'] === true) {
                $user->update([
                    'email_verified_at' => now()
                ]);

                Mail::to($user->email)->send(
                    new ThankYouForVerificationMail($user->name ?? $user->email)
                );

                $message = 'Email verified successfully!';
            } else {
                $tempToken = bin2hex(random_bytes(32));

                $user->update([
                    'remember_token' => $tempToken,
                    'email_verified_at' => now()
                ]);

                $message = 'Reset code verified successfully!';
                $responseData['token'] = $tempToken;
            }

            $resetRecord->delete();
            DB::commit();

            return $this->successResponse(array_merge([
                'message' => $message,
            ], $responseData));
        } catch (\Exception $exp) {
            DB::rollBack();

            Log::error("Code verification failed", [
                "type"         => !empty($params['verifyEmail']) ? "email_verification_failed" : "password_reset_code_verification_failed",
                "server_error" => true,
                "email"        => $params['email'] ?? null,
                "exception"    => $exp->getMessage()
            ]);

            return $this->errorResponse('Failed to verify code.', 500, [
                'error' => $exp->getMessage()
            ]);
        }
    }


    public function asController(ActionRequest $request)
    {
        return $this->handle($request->validated());
    }
}
