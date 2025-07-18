<?php

namespace App\Actions\User;

use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ChangePassword
{
    use AsAction;
    use ApiResponse;

    public function rules(): array
    {
        return [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
            'new_password_confirmation' => 'required|string|min:8',
        ];
    }

    public function handle(array $params)
    {
        try {
            $user = auth()->user();

            $currentPassword = $params['current_password'];
            $newPassword = $params['new_password'];
            $newPasswordConfirmation = $params['new_password_confirmation'];

            if ($newPassword !== $newPasswordConfirmation) {
                return $this->errorResponse('New password and confirmation do not match', 422, [
                    'error' => 'Password confirmation mismatch'
                ]);
            }

            if ($currentPassword === $params['new_password']) {
                return $this->errorResponse('New password cannot be the same as current password', 422, [
                    'error' => 'New password must be different from current password'
                ]);
            }

            if (!password_verify($currentPassword, $user->password)) {
                return $this->errorResponse('Current password is incorrect', 403, [
                    'error' => 'Invalid current password'
                ]);
            }

            $user->password = Hash::make($newPassword);
            $user->save();

            return $this->successResponse([
                'message' => 'Password changed successfully'
            ], 'Password changed successfully');

        } catch (\Exception $e) {
            Log::error("Changing password failed", [
                "type" => "change_password_failed",
                "server_error" => true,
                "exception" => $e->getMessage(),
                "user_id" => auth()->id()
            ]);
            return $this->errorResponse('Change password failed.', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function asController(ActionRequest $request)
    {
        return $this->handle($request->all());
    }


}
