<?php

namespace App\Actions\User;

use App\Models\Customer;
use App\Models\Task;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class Dashboard
{
    use AsAction, ApiResponse;

    public function rules(): array
    {
        return [
            'user_id' => 'sometimes|integer|exists:users,id',
        ];
    }

    public function handle(array $params)
    {
        try {
            $user = auth()->user();

            // Use provided user_id if available, otherwise fallback to auth user
            $user_id = $params['user_id'] ?? $user->id;

            $stats = [
                'customers'     => Customer::where("user_id", $user_id)->count(),
                'ongoing_tasks' => Task::where("user_id", $user_id)
                    ->where("status", "upcoming")
                    ->count(),
                'overdue_tasks' => Task::where("user_id", $user_id)
                    ->where("status", "overdue")
                    ->where("due_date", "<", now())
                    ->count(),
            ];

            return $this->successResponse($stats, 'Statistics retrieved successfully');
        } catch (\Exception $e) {
            Log::error("Fetching dashboard failed", [
                "type"        => "fetch_dashboard_failed",
                "server_error" => true,
                "exception"   => $e->getMessage(),
                "user_id"     => auth()->id(),
            ]);

            return $this->errorResponse('Unable to fetch statistics.', 500, [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function asController(ActionRequest $request)
    {
        return $this->handle($request->validated());
    }
}
