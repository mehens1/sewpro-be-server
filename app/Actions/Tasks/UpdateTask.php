<?php

namespace App\Actions\Tasks;

use App\Models\Task;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateTask
{
    use AsAction, ApiResponse;

    public function rules()
    {
        return [
            'task_id' => 'sometimes|integer|exists:tasks,id',
            'title'    => 'required|string',
            'description'    => 'nullable|string',
            'collection_date'    => 'sometimes|date',
            'due_date'    => 'required|date',
            'status'  => ['required', Rule::in(['upcoming', 'done', 'overdue'])],
        ];
    }

    public function handle(array $params)
    {
        try {

            $task = Task::findOrFail($params['task_id']);

            // Update the task
            $task->update([
                'title' => $params['title'],
                'description' => $params['description'] ?? null,
                'collection_date' => $params['collection_date'],
                'due_date' => $params['due_date'],
                'status' => $params['status'],
            ]);

            return $this->successResponse($task, 'Task updated successfully', 200);
        } catch (\Throwable $th) {
            Log::error("Error updating task: " . $th->getMessage());

            return $this->errorResponse(
                'Failed to update task.',
                500,
                ['error' => $th->getMessage()]
            );
        }
    }


    public function asController(ActionRequest $request, $id)
    {
        $validated = $request->validated();
        $validated['task_id'] = $id;

        return $this->handle($validated);
    }
}
