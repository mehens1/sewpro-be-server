<?php

namespace App\Actions\Measurements;

use App\Traits\ApiResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\ActionRequest;
use App\Models\ClothType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeleteMeasurement
{
    use AsAction, ApiResponse;

    public function rules(): array
    {
        return [
            // 'cloth_type_id' => 'required|exists:cloth_types,id',
        ];
    }

    public function handle(int $clothTypeId)
    {
        // return $clothTypeId;

        DB::beginTransaction();

        try {
            $clothType = ClothType::with(['measurements', 'customer'])->findOrFail($clothTypeId);

            if ($clothType->customer->user_id !== auth()->id()) {
                return $this->errorResponse('You are not authorized to delete this measurement..', 403);
            }

            $clothType->measurements()->delete();

            $clothType->delete();

            DB::commit();

            return $this->successResponse(
                null,
                "Cloth type and its measurements deleted successfully."
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Failed to delete cloth type', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse('Failed to delete measurement.', 500, [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function asController(ActionRequest $request, $id)
    {
        return $this->handle((int) $id);
    }
}
