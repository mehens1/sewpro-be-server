<?php

namespace App\Actions\Company;

use App\Models\CompanyDetail;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\ActionRequest;

class GetUserCompany
{
    use AsAction, ApiResponse;

    /**
     * Retrieve company details for the logged-in user
     */
    public function handle()
    {
        try {
            $user = auth()->user();

            $company = CompanyDetail::where('user_id', $user->id)->first();

            if (!$company) {
                return $this->errorResponse('Company details not found.', 404);
            }

            return $this->successResponse(
                $company,
                'Company details retrieved successfully.'
            );
        } catch (\Throwable $e) {
            Log::error('Failed to retrieve company details', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse('Failed to retrieve company details.', 500, [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Controller entry point
     */
    public function asController(ActionRequest $request)
    {
        return $this->handle();
    }
}
