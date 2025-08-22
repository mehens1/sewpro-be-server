<?php

namespace App\Actions\Customer;

use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Models\Customer;
use App\Services\FileUploadService;

class CreateCustomer
{
    use AsAction;
    use ApiResponse;

    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    private const NULLABLE_STRING = 'nullable|string';

    public function rules(): array
    {
        return [
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => self::NULLABLE_STRING . '|max:15',
            'date_of_birth' => 'nullable|date',
            'gender' => self::NULLABLE_STRING . '|in:male,female,other',
            'profile_picture' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'nationality' => self::NULLABLE_STRING,
            'address' => self::NULLABLE_STRING,
        ];
    }

    public function handle(array $params, $file = null)
    {
        try {
            $user = auth()->user();

            $existingCustomer = Customer::where('user_id', $user->id)
                ->where(function ($query) use ($params) {
                    if (!empty($params['email'])) {
                        $query->orWhere('email', $params['email']);
                    }
                    if (!empty($params['phone'])) {
                        $query->orWhere('phone', $params['phone']);
                    }
                })
                ->first();

            if ($existingCustomer) {
                return $this->errorResponse('Customer with this email or phone already exists.', 422, [
                    'error' => 'Customer with this email or phone already exists.'
                ]);
            }

            $uploadedFileUrl = null;
            if ($file) {
                $folder = "customers/{$user->id}";
                $uploadedFileUrl = $this->fileUploadService->uploadFile($file, $folder);
            }

            $newCustomer = Customer::create([
                'user_id' => $user->id,
                'full_name' => $params['full_name'],
                'email' => $params['email'] ?? null,
                'phone' => $params['phone'] ?? null,
                'date_of_birth' => $params['date_of_birth'] ?? null,
                'gender' => $params['gender'] ?? null,
                'profile_picture' => $uploadedFileUrl,
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
        return $this->handle(
            $request->all(),
            $request->file('profile_picture')
        );
    }
}
