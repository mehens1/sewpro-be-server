<?php

namespace App\Actions\Customer;

use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Models\Customer;

class getCustomers
{
    use AsAction;
    use ApiResponse;

    public function rules(): array
    {
        return [
            'per_page' => 'integer|min:1|max:100',
            'page'     => 'integer|min:1',
            'search'   => 'string|nullable',
            'all'      => 'boolean',
        ];
    }

    public function handle(array $params)
    {
        try {
            $user = auth()->user();

            $perPage = $params['per_page'] ?? 10;
            $search  = $params['search'] ?? null;
            $allFlag = !empty($params['all']) && filter_var($params['all'], FILTER_VALIDATE_BOOLEAN);

            $query = Customer::query();

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                });
            }

            if (!$allFlag || !$user->is_staff) {
                $query->where('user_id', $user->id);
            }

            $customers = $query->paginate($perPage);

            return $this->successResponse([
                'message' => 'All Customers retrieved successfully',
                'customers' => $customers
            ]);
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

    public function asController(ActionRequest $request)
    {
        return $this->handle($request->all());
    }
}
