<?php

namespace App\Actions\Customer;

use App\Models\Customer;
use App\Services\FileUploadService;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateCustomer
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

    public function handle($id, array $params, $file = null)
    {
        try {
            $user = auth()->user();

            $customer = Customer::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$customer) {
                return $this->errorResponse('Customer not found.', 404);
            }

            $duplicate = Customer::where('user_id', $user->id)
                ->where(function ($query) use ($params) {
                    if (!empty($params['email'])) {
                        $query->orWhere('email', $params['email']);
                    }
                    if (!empty($params['phone'])) {
                        $query->orWhere('phone', $params['phone']);
                    }
                })
                ->where('id', '!=', $customer->id)
                ->first();

            if ($duplicate) {
                return $this->errorResponse('Another customer with this email or phone already exists.', 422);
            }

            if ($file) {
                $folder = "customers/{$user->id}";
                $uploadedFileUrl = $this->fileUploadService->uploadFile($file, $folder);
                $customer->profile_picture = $uploadedFileUrl;
            }

            $customer->full_name = $params['full_name'];
            $customer->email = $params['email'] ?? null;
            $customer->phone = $params['phone'] ?? null;
            $customer->date_of_birth = $params['date_of_birth'] ?? null;
            $customer->gender = $params['gender'] ?? null;
            $customer->nationality = $params['nationality'] ?? null;
            $customer->address = $params['address'] ?? null;

            $customer->save();

            return $this->successResponse([
                'message' => 'Customer updated successfully',
                'user' => $customer
            ]);
        } catch (\Exception $e) {
            Log::error("Updating customer failed", [
                "type" => "update_customer_failed",
                "server_error" => true,
                "exception" => $e->getMessage(),
                "user_id" => auth()->id()
            ]);

            return $this->errorResponse('Updating customer failed.', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function asController(ActionRequest $request, $id)
    {
        return $this->handle(
            $id,
            $request->all(),
            $request->file('profile_picture')
        );
    }
}
