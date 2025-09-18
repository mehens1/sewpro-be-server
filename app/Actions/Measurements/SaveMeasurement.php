<?php

namespace App\Actions\Measurements;

use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Models\ClothType;
use App\Models\Measurement;
use Illuminate\Support\Facades\DB;

class SaveMeasurement
{
    use AsAction, ApiResponse;

    public function rules(): array
    {
        return [
            'customer_id'           => 'required|exists:customers,id',
            'cloth_types'           => 'required|array',
            'cloth_types.*.name'    => 'required|string',
            'cloth_types.*.measurements' => 'required|array',
        ];
    }

    public function handle(array $params)
    {
        DB::beginTransaction();

        try {
            $savedClothTypes = [];

            if (!isset($params) || !isset($params["customer_id"])) {
                return $this->errorResponse('Failed to save measurements, cant create a measurement at random', 404);
            }

            foreach ($params['cloth_types'] as $clothData) {
                // Check if this cloth type already exists for the customer
                $existingCloth = ClothType::where('customer_id', $params['customer_id'])
                    ->where('name', $clothData['name'])
                    ->first();

                if ($existingCloth) {
                    DB::rollBack(); // rollback transaction
                    return $this->errorResponse(
                        "Cloth type '{$clothData['name']}' already exists for this customer.",
                        422,
                        ['error' => "Cloth type '{$clothData['name']}' already exists for this customer."]
                    );
                }

                // Create cloth type
                $clothType = ClothType::create([
                    'customer_id' => $params['customer_id'],
                    'name'        => $clothData['name'],
                ]);

                // Save measurements
                foreach ($clothData['measurements'] as $fieldName => $fieldValue) {
                    Measurement::create([
                        'cloth_type_id' => $clothType->id,
                        'field_name'    => $fieldName,
                        'field_value'   => $fieldValue,
                    ]);
                }

                $savedClothTypes[] = $clothType->load('measurements');
            }

            DB::commit();
            return $this->successResponse($savedClothTypes, 'Measurements saved successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Failed to save measurements', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse('Failed to save measurements.', 500, [
                'error' => $e->getMessage(),
            ]);
        }
    }


    public function asController(ActionRequest $request)
    {
        return $this->handle($request->all());
    }
}
