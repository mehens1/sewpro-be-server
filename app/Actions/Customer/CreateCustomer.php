<?php

namespace App\Actions\Customer;

use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Models\Customer;

class CreateCustomer
{
    use AsAction;
    use ApiResponse;

    private const NULLABLE_STRING = 'nullable|string';

    public function rules(): array
    {
        return [
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email',
            'phone' => self::NULLABLE_STRING . '|unique:customers,phone|max:15',
            'date_of_birth' => 'nullable|date',
            'gender' => self::NULLABLE_STRING . '|in:male,female,other',
            'profile_picture' => self::NULLABLE_STRING,
            'nationality' => self::NULLABLE_STRING,
            'address' => self::NULLABLE_STRING,
        ];
    }

    public function handle(array $params)
    {
        try {
            $user = auth()->user();

            $newCustomer = Customer::create([
                'user_id' => $user->id,
                'full_name' => $params['full_name'],
                'email' => $params['email'] ?? null,
                'phone' => $params['phone'] ?? null,
                'date_of_birth' => $params['date_of_birth'] ?? null,
                'gender' => $params['gender'] ?? null,
                'profile_picture' => $params['profile_picture'] ?? null,
                'nationality' => $params['nationality'] ?? null,
                'address' => $params['address'] ?? null,
            ]);

            return $this->successResponse([
                'message' => 'Customer created successfully',
                'user' => $newCustomer
            ], 'Customer created successfully');
        } catch (\Exception $e) {
            Log::error("Creating customer failed", [
                "type" => "create_customer_failed",
                "server_error" => true,
                "exception" => $e->getMessage(),
                "user_id" => auth()->id()
            ]);

            return $this->errorResponse('Creating customer failed.', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function asController(ActionRequest $request)
    {
        return $this->handle($request->all());
    }
}
