<?php

namespace App\Actions\Tasks;

use App\Models\Task;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteTask
{
    use AsAction, ApiResponse;

    public function handle(String $id)
    {
        try {
            $user = auth()->user();

            // Find the user's task
            $task = Task::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$task) {
                return $this->errorResponse('Task not found.', 404);
            }

            // Update status
            $task->delete();

            return $this->successResponse($task, 'Task deleted successfully', 200);
        } catch (\Throwable $th) {
            Log::error("Error deleting task: " . $th->getMessage());

            return $this->errorResponse(
                'Failed to deletes task.',
                500,
                ['error' => $th->getMessage()]
            );
        }
    }

    public function asController($task_id)
    {
        return $this->handle($task_id);
    }
}
