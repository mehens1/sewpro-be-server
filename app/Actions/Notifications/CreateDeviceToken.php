<?php

namespace App\Actions\Notifications;

use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Models\ClothType;
use App\Models\Measurement;
use App\Models\UserDevice;
use Illuminate\Support\Facades\DB;

class CreateDeviceToken
{
    use AsAction, ApiResponse;

    public function rules(): array
    {
        return [
            'token' => 'required|string',
            'device_id' => 'nullable|string',
            'platform' => 'nullable|in:android,ios,web',
        ];
    }

    public function handle(array $params)
    {

        try {
            $user = auth()->user();

            $device = UserDevice::updateOrCreate(
                [
                    'user_id'   => $user->id,
                    'device_id' => $params['device_id'],
                ],
                [
                    'token'        => $params['token'],
                    'platform'     => $params['platform'] ?? null,
                    'last_seen_at' => now(),
                ]
            );


            return $this->successResponse($device, 'Device Saved Successfully!', 201);
        } catch (\Throwable $e) {

            Log::error('Failed to save measurements', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse('Failed to save user device.', 500, [
                'error' => $e->getMessage(),
            ]);
        }
    }


    public function asController(ActionRequest $request)
    {
        return $this->handle($request->all());
    }
}
