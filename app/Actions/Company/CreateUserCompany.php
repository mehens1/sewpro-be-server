<?php

namespace App\Actions\Company;

use App\Models\CompanyDetail;
use App\Services\FileUploadService;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\ActionRequest;

class CreateUserCompany
{
    use AsAction, ApiResponse;

    protected FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    public function rules(): array
    {
        return [
            'name'                => 'required|string|max:255',
            'address'             => 'nullable|string|max:500',
            'phone'               => 'nullable|string|max:50',
            'email'               => 'nullable|email|max:255',
            'logo_path'           => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
            'bank_name'           => 'nullable|string|max:255',
            'bank_account_name'   => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:50',
        ];
    }

    /**
     * Handles saving/updating company details for the logged-in user
     */
    public function handle(array $params)
    {
        try {
            $user = auth()->user();

            $company = DB::transaction(function () use ($user, $params) {
                $uploadedFileUrl = null;

                if (isset($params['logo_path']) && $params['logo_path']) {
                    $folder = "users/{$user->id}/company";
                    $uploadedFileUrl = $this->fileUploadService->uploadFile(
                        $params['logo_path'],
                        $folder
                    );
                }

                $data = [
                    'name'                => $params['name'],
                    'address'             => $params['address'] ?? null,
                    'phone'               => $params['phone'] ?? null,
                    'email'               => $params['email'] ?? null,
                    'bank_name'           => $params['bank_name'] ?? null,
                    'bank_account_name'   => $params['bank_account_name'] ?? null,
                    'bank_account_number' => $params['bank_account_number'] ?? null,
                ];

                if (!empty($uploadedFileUrl)) {
                    $data['logo_path'] = $uploadedFileUrl;
                }

                return CompanyDetail::updateOrCreate(
                    ['user_id' => $user->id],
                    $data
                );
            });

            return $this->successResponse(
                $company,
                'Company details saved successfully.',
                201
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Failed to save company details', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse('Failed to save company details.', 500, [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Controller entry point
     */
    public function asController(ActionRequest $request)
    {
        return $this->handle($request->validated());
    }
}
