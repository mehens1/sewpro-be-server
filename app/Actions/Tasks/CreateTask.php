<?php

namespace App\Actions\Tasks;

use App\Models\Task;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateTask
{
    use AsAction, ApiResponse;

    public function rules()
    {
        return [
            'title'    => 'required|string',
            'description'    => 'nullable|string',
            'collection_date'    => 'nullable|date',
            'due_date'    => 'required|date',
            'status'   => ['sometimes', Rule::in(['upcoming', 'done', 'overdue'])],

        ];
    }

    public function handle(array $params)
    {
        try {
            $user = auth()->user();

            $exists = Task::where('user_id', $user->id)
                ->where('title', $params['title'])
                ->whereDate('due_date', $params['due_date'])
                ->exists();

            if ($exists) {
                return $this->errorResponse(
                    'You already created this task.',
                    422
                );
            }

            $task = Task::create([
                'user_id'        => $user->id,
                'title'          => $params['title'],
                'description'    => $params['description'] ?? null,
                'collection_date' => $params['collection_date'] ?? null,
                'due_date'       => $params['due_date'],
                'status'         => $params['status'] ?? 'upcoming',
            ]);

            return $this->successResponse($task, 'Task created successfully', 201);
        } catch (\Throwable $th) {
            Log::error("Error creating task: " . $th->getMessage());

            return $this->errorResponse(
                'Failed to create task.',
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
