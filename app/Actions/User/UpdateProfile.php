<?php

namespace App\Actions\User;

use App\Models\User;
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
            'gender' => 'nullable|string',
            'residential_address' => 'nullable|string',
            'bio' => 'nullable|string',
        ];
    }

    public function handle(array $params)
    {
        try {
            $user = auth()->user();

            $fullUser = User::with('detail')->findOrFail($user->id);

            if (isset($params['phone_number'])) {
                $fullUser->phone_number = $params['phone_number'];
                $fullUser->save();
            }

            unset($params['phone_number']);

            if (!empty($params)) {
                $fullUser->detail()->updateOrCreate(
                    ['user_id' => $fullUser->id],
                    $params
                );
            }

            return $this->successResponse([
                'message' => 'Profile updated successfully',
                'user' => $fullUser->fresh()->load('detail'),
            ]);
        } catch (\Exception $e) {
            Log::error("Updating profile failed", [
                "type" => "update_profile_failed",
                "server_error" => true,
                "exception" => $e->getMessage(),
                "user_id" => auth()->id(),
            ]);
            return $this->errorResponse('Update profile failed.', 500, [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function asController(ActionRequest $request)
    {
        return $this->handle($request->validated());
    }
}
