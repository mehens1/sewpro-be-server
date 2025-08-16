<?php

namespace App\Actions\Measurements;

use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\ActionRequest;
use App\Models\ClothType;

class GetMeasurement
{
    use AsAction, ApiResponse;

    public function handle(int $customerId)
    {
        // Fetch all cloth types with their measurements for this customer
        $clothTypes = ClothType::with('measurements')
            ->where('customer_id', $customerId)
            ->get();

        return $this->successResponse($clothTypes, 'Measurements saved successfully.');
    }

    public function asController(ActionRequest $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id'
        ]);

        return $this->handle($request->customer_id);
    }
}
