<?php

namespace App\Actions\Customer;

use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Models\Customer;

class DeleteCustomer
{
    use AsAction;
    use ApiResponse;

    public function rules(): array
    {
        return [
            // 'per_page' => 'integer|min:1|max:100',
            // 'page'     => 'integer|min:1',
            // 'search'   => 'string|nullable',
            // 'all'      => 'boolean',
        ];
    }

    public function handle($id)
    {
        try {
            // $user = auth()->user();

            $customer = Customer::findOrFail($id);

            $customer->delete();

            return $this->successResponse([
                'message' => 'Customer deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error("Deleting customer failed", [
                "type" => "deleting_customer_failed",
                "server_error" => true,
                "exception" => $e->getMessage(),
                "user_id" => auth()->id()
            ]);

            return $this->errorResponse('Deleting customer failed.', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }


    public function asController(ActionRequest $request, $id)
    {
        return $this->handle($id);
    }
}
