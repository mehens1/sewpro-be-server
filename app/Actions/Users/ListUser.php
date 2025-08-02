<?php

namespace App\Actions\Users;

use App\Traits\ApiResponse;

use Illuminate\Support\Facades\Log;

use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

use App\Models\User;
use App\Models\Staff;

class ListUser
{
    use AsAction;
    use ApiResponse;

    public function rules(): array
    {
        return [];
    }

    public function handle(array $params)
    {
        try {
            $user = auth()->user();

            if (!$user->is_staff) {
                return $this->errorResponse('You are not permitted to access this route', 403, [
                    'error' => 'You are not permitted'
                ]);
            }

            $users = User::get();

            return $this->successResponse([
                'users' => $users
            ], 'Users fetched successfully');
        } catch (\Exception $e) {
            Log::error("Fetching users failed", [
                "type" => "list_users_failed",
                "server_error" => true,
                "exception" => $e->getMessage(),
                "user_id" => auth()->id()
            ]);

            return $this->errorResponse('An error occurred while fetching users', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }


    public function asController(ActionRequest $request)
    {
        return $this->handle($request->all());
    }
}
