<?php

namespace App\Actions\Invoices;

use App\Models\Invoice;
use App\Traits\ApiResponse;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Support\Facades\Log;

class ListCustomerInvoices
{
    use AsAction, ApiResponse;

    public function rules()
    {
        return [
            // 'customer_id' => 'required|exists:customers,id',
            'status' => 'sometimes|' . Rule::in(['pending', 'paid', 'cancelled']),
            'per_page' => 'sometimes|integer|min:1|max:100',
            'page' => 'sometimes|integer|min:1',
        ];
    }

    public function handle(array $params)
    {
        try {
            $query = Invoice::where('customer_id', $params['customer_id']);

            if (isset($params['status'])) {
                $query->where('status', $params['status']);
            }

            $perPage = $params['per_page'] ?? 10;
            $page = $params['page'] ?? 1;

            return $query->with('items')->latest()->paginate($perPage);
        } catch (\Throwable $th) {
            Log::debug("Error fetching customer invoices: ", [$th]);

            return $this->errorResponse(
                'Failed to fetch invoices.',
                500,
                ['error' => $th->getMessage()]
            );
        }
    }


    public function asController(ActionRequest $request, $customer_id)
    {
        $params = $request->validated();
        $params['customer_id'] = $customer_id;
        return $this->handle($params);
    }
}
