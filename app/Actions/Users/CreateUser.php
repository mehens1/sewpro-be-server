<?php

namespace App\Actions\Users;

use App\Models\Staff;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateUser
{
    use AsAction, ApiResponse;

    public function rules(): array
    {
        return [
            'first_name'    => 'required|string|max:100',
            'last_name'     => 'required|string|max:100',
            'email'         => 'required|email|unique:users,email',
            'phone_number'  => 'required|string|unique:users,phone_number',
        ];
    }

    public function handle(array $params)
    {
        try {
            $authUser = auth()->user();

            if (!$authUser || $authUser->account_type !== 'staff') {
                return $this->errorResponse('You are not permitted to access this route', 403);
            }

            $password = config('auth.default_user_password');

            // 1. Create the user
            $user = User::create([
                'email'         => $params['email'],
                'phone_number'  => $params['phone_number'],
                'account_type'  => 'staff',
                'user_role'     => null,
                'password'      => Hash::make($password),
            ]);

            // 2. Create related staff
            $staff = Staff::create([
                'user_id'    => $user->id,
                'first_name' => $params['first_name'],
                'last_name'  => $params['last_name'],
            ]);

            return $this->successResponse([
                'user'  => $user->load('staff'),
            ], 'User created successfully');

        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            Log::error("CreateUser failed", [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'context' => $params,
            ]);

            return $this->errorResponse('Server error while creating user', 500);
        }
    }

    public function asController(ActionRequest $request)
    {
        return $this->handle($request->validated());
    }

    public function authorize(ActionRequest $request): bool
    {
        return true;
    }
}
