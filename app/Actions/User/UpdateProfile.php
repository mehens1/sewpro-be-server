<?php

namespace App\Actions\User;

use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateProfile
{
    use AsAction;
    use ApiResponse;

    public function rules(): array
    {
        return [
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'phone_number' => 'sometimes|string|max:20|unique:users,phone_number,' . auth()->id(),
        ];
    }

    public function handle(array $params)
    {
        try {
            $user = auth()->user();

            // Update phone number on the users table
            if (isset($params['phone_number'])) {
                $user->phone_number = $params['phone_number'];
                $user->save();
            }

            // Remove phone_number from params to avoid passing it to related tables
            unset($params['phone_number']);

            // Update related profile table based on account type
            if (!empty($params)) {
                switch ($user->account_type) {
                    case 'staff':
                        if ($user->staff) {
                            $user->staff->update($params);
                        }
                        break;

                    case 'tailor':
                        if ($user->tailor) {
                            $user->tailor->update($params);
                        }
                        break;

                    default:
                        // Optional: log or handle unknown account types
                        Log::warning("Unknown account type when updating profile", [
                            "user_id" => $user->id,
                            "account_type" => $user->account_type
                        ]);
                        break;
                }
            }

            return $this->successResponse([
                'message' => 'Profile updated successfully',
                'user' => $user->fresh()
            ], 'Profile updated successfully');

        } catch (\Exception $e) {
            Log::error("Updating profile failed", [
                "type" => "update_profile_failed",
                "server_error" => true,
                "exception" => $e->getMessage(),
                "user_id" => auth()->id()
            ]);
            return $this->errorResponse('Update profile failed.', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function asController(ActionRequest $request)
    {
        return $this->handle($request->all());
    }


}
