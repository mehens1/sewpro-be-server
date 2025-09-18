<?php

namespace App\Actions\Customer;

use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Models\Customer;

class getCustomer
{
    use AsAction;
    use ApiResponse;

    public function handle(int $id)
    {
        try {
            $user = auth()->user();

            $customer = Customer::where('user_id', $user->id)
                ->where('id', $id)
                ->first();

            return $this->successResponse(
                $customer,
                'Customer retrieved successfully'
            );
        } catch (\Exception $e) {
            Log::error("Fetching customer failed", [
                "type" => "fetching_customer_failed",
                "server_error" => true,
                "exception" => $e->getMessage(),
                "user_id" => auth()->id()
            ]);

            return $this->errorResponse('Fetching customer failed.', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function asController(ActionRequest $request, $id)
    {
        return $this->handle($id);
    }
}
