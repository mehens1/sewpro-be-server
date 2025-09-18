<?php

namespace App\Actions\User;

use App\Models\User;
use App\Services\FileUploadService;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateProfile
{
    use AsAction, ApiResponse;

    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'phone_number' => 'sometimes|string|max:20|unique:users,phone_number,' . auth()->id(),
            'gender' => 'nullable|string',
            'residential_address' => 'nullable|string',
            'profile_picture' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'bio' => 'nullable|string',
        ];
    }

    public function handle(array $params)
    {
        try {
            $user = auth()->user();
            $fullUser = User::with('detail')->findOrFail($user->id);

            if (!empty($params['phone_number'])) {
                $fullUser->update(['phone_number' => $params['phone_number']]);
            }

            // Handle profile picture upload
            if (!empty($params['profile_picture'])) {
                $folder = "users/{$user->id}";
                $uploadedFileUrl = $this->fileUploadService->uploadFile($params['profile_picture'], $folder);
                $params['profile_picture'] = $uploadedFileUrl;
            }

            $detailParams = collect($params)->except(['phone_number'])->toArray();
            if (!empty($detailParams)) {
                $fullUser->detail()->updateOrCreate(
                    ['user_id' => $fullUser->id],
                    $detailParams
                );
            }

            return $this->successResponse([
                'message' => 'Profile updated successfully',
                'user' => $fullUser->fresh()->load('detail'),
            ]);
        } catch (\Exception $e) {
            Log::error("Updating profile failed", [
                "type" => "update_profile_failed",
                "server_error" => true,
                "exception" => $e->getMessage(),
                "user_id" => auth()->id(),
            ]);
            return $this->errorResponse('Update profile failed.', 500, [
                'error' => $e->getMessage(),
            ]);
        }
    }


    public function asController(ActionRequest $request)
    {
        return $this->handle($request->validated());
    }
}
