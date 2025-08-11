<?php

namespace App\Actions\Auth;

use App\Traits\ApiResponse;

use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class Login
{
    use AsAction;
    use ApiResponse;

    public function rules(): array
    {
        return [
            'username' => 'required|string',
            'password' => 'required|string|min:8',
            'from_admin' => 'nullable|boolean',
            'from_mobile' => 'nullable|boolean',
        ];
    }

    public function handle(array $params)
    {
        $username = $params['username'];
        $password = $params['password'];
        $isAdmin = $params['from_admin'] ?? false;
        $isMobile = $params['from_mobile'] ?? false;

        if (!$isAdmin && !$isMobile) {
            return $this->errorResponse('Authentication is only allowed for admin or mobile clients.', 400, [
                'error' => 'unknown_client',
            ]);
        }

        if (!filter_var($username, FILTER_VALIDATE_EMAIL) && !preg_match('/^\+?[0-9]{10,15}$/', $username)) {
            return $this->errorResponse('Invalid username format. Please use a valid email or phone number.', 400, [
                'error' => 'invalid_username_format',
            ]);
        }

        try {
            $credentials = filter_var($username, FILTER_VALIDATE_EMAIL)
                ? ['email' => $username, 'password' => $password]
                : ['phone_number' => $username, 'password' => $password];

            if (!$token = JWTAuth::attempt($credentials)) {
                return $this->errorResponse("The email or password you entered is incorrect", 401, [
                    "error" => "invalid_credentials"
                ]);
            }

            $user = auth()->user()->load(['detail']);

            if ($isAdmin && !$user->is_staff) {
                return $this->errorResponse('Access Denied! You do not have permission to this system.', 400, [
                    'error' => 'Permission Denied! You do not have permission to this system.',
                ]);
            }

            return $this->successResponse([
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
                'user' => $user
            ], 'Login successful');
        } catch (\Exception $e) {
            Log::error("Login failed", [
                "type" => "login_failed",
                "server_error" => true,
                "exception" => $e->getMessage(),
                "username" => $username
            ]);
            return $this->errorResponse('An error occurred while logging in user', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function asController(ActionRequest $request)
    {
        return $this->handle($request->all());
    }
}
