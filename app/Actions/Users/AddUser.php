<?php

namespace App\Actions\Users;

use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AddUser
{
    use AsAction;
    use ApiResponse;

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone_number' => 'required|string|max:15',
            'user_role' => 'sometimes|string',
        ];
    }

    public function handle(array $params)
    {
        try {
            $user = auth()->user();

            // create new user
            $newUser = User::create([
                'email' => $params['email'],
                'phone_number' => $params['phone_number'],
                'password' => Hash::make(config('app.default_user_password')),
                'is_staff' => 1,
                'user_role' => $params['user_role'] ?? 'staff',
            ]);

            // if user created, then create record in staff table
            $newUser->staff()->create([
                'user_id' => $newUser->id,
                'first_name' => $params['first_name'],
                'last_name' => $params['last_name'],
            ]);

            return $this->successResponse([
                'message' => 'User added successfully',
                'user' => $newUser
            ], 'User added successfully');
        } catch (\Exception $e) {
            Log::error("Adding user failed", [
                "type" => "add_user_failed",
                "server_error" => true,
                "exception" => $e->getMessage(),
                "user_id" => auth()->id()
            ]);
            return $this->errorResponse('Adding user failed.', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function asController(ActionRequest $request)
    {
        return $this->handle($request->all());
    }
}
