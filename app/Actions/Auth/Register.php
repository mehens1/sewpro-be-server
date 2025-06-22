<?php

namespace App\Actions\Auth;

use App\Traits\ApiResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\ActionRequest;
use Illuminate\Support\Facades\Hash;
use App\Enums\AccountType;
use App\Enums\UserRole;

use App\Models\User;

class Register
{
    use AsAction;
    use ApiResponse;

    public function rules()
    {
        return [
            'email' => 'bail|required|email|unique:users,email',
            'phone_number' => 'required|string|max:20|unique:users,phone_number',
            'password' => 'required|string|min:8',
            'account_type' => 'sometimes|in:' . implode(',', AccountType::values()),
            // 'user_role' => 'nullable|in:' . implode(',', UserRole::values()),
        ];
    }

    public function handle($details)
    {


        try {
            $existingEmail = User::where('email', $details['email'])->first();
            if ($existingEmail) {
                return $this->errorResponse('This email has been registered, please login.', 422, [
                    'error' => 'Email already exists.'
                ]);
            }

            $existingPhone = User::where('phone_number', $details['phone_number'])->first();
            if ($existingPhone) {
                return $this->errorResponse('This phone number has been registered, please login.', 422, [
                    'error' => 'Phone number already exists.'
                ]);
            }

            $user = User::create([
                'email' => $details['email'],
                'phone_number' => $details['phone_number'],
                'account_type' => $details['account_type'] ?? AccountType::TAILOR->value,
                'password' => Hash::make($details['password']),
            ]);

            return $this->successResponse([
                'message' => 'User registered successfully, Please Login.',
                'data'    => $user
            ], 201);


        } catch (\Exception $exp) {
            \Log::error("User registration failed", ["type" => "user_registration_failed", "server_error" => true, "email" => $details["email"], "exception" => $exp]);
            return $this->errorResponse('User registration failed.', 500, [
                'error' => $exp->getMessage()
            ]);
        }
    }

    public function asController(ActionRequest $request)
    {
        return $this->handle($request->validated());
    }
}
