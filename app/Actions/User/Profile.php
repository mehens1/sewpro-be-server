<?php

namespace App\Actions\User;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class Profile
{
    use AsAction, ApiResponse;

    public function rules(): array
    {
        return [
            'user_id' => 'sometimes|integer|exists:users,id',
        ];
    }

    public function handle(array $params)
    {

        // return $params;
        try {
            $user = isset($params['user_id'])
                ? User::with(['detail',])->findOrFail($params['user_id'])
                : auth()->user()->load(['detail',]);

            return $this->successResponse([
                'message' => 'Profile retrieved successfully',
                'user'    => $user,
            ]);
        } catch (\Exception $e) {
            Log::error("Fetching profile failed", [
                "type"        => "fetch_profile_failed",
                "server_error" => true,
                "exception"   => $e->getMessage(),
                "user_id"     => auth()->id(),
            ]);

            return $this->errorResponse('Unable to fetch profile.', 500, [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function asController(ActionRequest $request)
    {
        return $this->handle($request->validated());
    }
}
