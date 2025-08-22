<?php

namespace App\Actions\Measurements;

use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Models\ClothType;

class GetMeasurement
{
    use AsAction, ApiResponse;

    public function handle(int $customerId)
    {
        try {
            $user = auth()->user();

            $clothTypes = ClothType::where('customer_id', $customerId)
                ->whereHas('customer', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->with('measurements')
                ->get();

            if ($clothTypes->isEmpty()) {
                return $this->errorResponse('No measurements found for this customer.', 404);
            }

            // Transform the response
            $response = [
                'customer_id' => $customerId,
                'cloth_types' => $clothTypes->map(function ($clothType) {
                    return [
                        'id' => $clothType->id,
                        'name' => $clothType->name,
                        'created_at' => $clothType->created_at,
                        'updated_at' => $clothType->updated_at,
                        'measurements' => $clothType->measurements->map(function ($m) {
                            return [
                                'id' => $m->id,
                                'cloth_type_id' => $m->cloth_type_id,
                                'field_name' => $m->field_name,
                                'field_value' => $m->field_value,
                                'created_at' => $m->created_at,
                                'updated_at' => $m->updated_at,
                            ];
                        })
                    ];
                })
            ];

            return $this->successResponse(
                $response,
                'Measurements retrieved successfully'
            );
        } catch (\Exception $e) {
            Log::error("Fetching measurement failed", [
                "type" => "fetching_measurement_failed",
                "server_error" => true,
                "exception" => $e->getMessage(),
                "user_id" => auth()->id()
            ]);

            return $this->errorResponse('Fetching measurement failed.', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function asController(ActionRequest $request, $id)
    {
        return $this->handle((int) $id);
    }
}
