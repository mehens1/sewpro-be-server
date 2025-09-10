<?php

namespace App\Actions\Tasks;

use App\Models\Task;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ListTasks
{
    use AsAction, ApiResponse;

    public function rules()
    {
        return [
            'status'   => ['sometimes', Rule::in(['upcoming', 'done', 'overdue'])],
            'title'    => 'sometimes|string',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'limit'    => 'sometimes|integer|min:1|max:100',
        ];
    }

    public function handle(array $params)
    {
        try {
            $user = auth()->user();

            $query = Task::where('user_id', $user->id);

            if (!empty($params['status'])) {
                $query->where('status', $params['status']);
            }

            if (!empty($params['title'])) {
                $query->where('title', 'like', '%' . $params['title'] . '%');
            }

            $query->latest();

            $perPage = $params['per_page'] ?? 10;

            if (!empty($params['limit'])) {
                // Clamp per_page so it never exceeds limit
                $perPage = min($perPage, $params['limit']);

                // Paginate but enforce max total results
                return $query->paginate(
                    $perPage,
                    ['*'],
                    'page',
                    null
                )->through(function ($task) use ($params) {
                    return $task;
                })->appends($params)->setCollection(
                    $query->take($params['limit'])->get()
                );
            }

            return $query->paginate($perPage);

            // if (!empty($params['limit'])) {
            //     return $query->take($params['limit'])->get();
            // }

            // $perPage = $params['per_page'] ?? 10;
            // return $query->paginate($perPage);
        } catch (\Throwable $th) {
            Log::error("Error fetching tasks: " . $th->getMessage());

            return $this->errorResponse(
                'Failed to fetch tasks.',
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
