<?php

namespace App\Actions\Tasks;

use App\Models\Task;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateTaskStatus
{
    use AsAction, ApiResponse;

    public function rules()
    {
        return [
            'task_id' => 'required|integer|exists:tasks,id',
            'status'  => ['required', Rule::in(['upcoming', 'done', 'overdue'])],
        ];
    }

    public function handle(array $params)
    {
        try {
            $user = auth()->user();

            // Find the user's task
            $task = Task::where('id', $params['task_id'])
                ->where('user_id', $user->id)
                ->first();

            if (! $task) {
                return $this->errorResponse('Task not found.', 404);
            }

            // Update status
            $task->update([
                'status' => $params['status'],
            ]);

            return $this->successResponse($task, 'Task status updated successfully', 200);
        } catch (\Throwable $th) {
            Log::error("Error updating task status: " . $th->getMessage());

            return $this->errorResponse(
                'Failed to update task status.',
                500,
                ['error' => $th->getMessage()]
            );
        }
    }

    public function asController(ActionRequest $request)
    {
        return $this->handle($request->validated());
    }
}
