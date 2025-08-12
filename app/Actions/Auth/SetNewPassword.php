<?php

namespace App\Actions\Auth;

use App\Traits\ApiResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\ActionRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class SetNewPassword
{
    use AsAction, ApiResponse;

    public function rules(): array
    {
        return [
            'token' => 'required|string',
            'new_password' => 'required|string|min:8',
            'confirm_password' => 'required|string|same:new_password',
        ];
    }

    public function handle(array $params)
    {
        DB::beginTransaction();

        try {
            $user = User::where('remember_token', $params['token'])->first();

            if (!$user) {
                return $this->errorResponse('Invalid or expired token.', 422, [
                    'error' => 'Token not valid.'
                ]);
            }

            $user->update([
                'password' => Hash::make($params['new_password']),
                'remember_token' => null
            ]);

            DB::commit();

            return $this->successResponse([
                'message' => 'Password has been updated successfully.'
            ]);
        } catch (\Exception $exp) {
            DB::rollBack();

            Log::error("Set new password failed", [
                "type"         => "set_new_password_failed",
                "server_error" => true,
                "token"        => $params['token'] ?? null,
                "exception"    => $exp->getMessage()
            ]);

            return $this->errorResponse('Failed to set new password.', 500, [
                'error' => $exp->getMessage()
            ]);
        }
    }

    public function asController(ActionRequest $request)
    {
        return $this->handle($request->validated());
    }
}
