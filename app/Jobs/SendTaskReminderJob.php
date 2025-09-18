<?php

namespace App\Jobs;

use App\Models\Task;
use Google\Client as GoogleClient;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendTaskReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Task $task;
    protected string $reminderType;

    public function __construct(Task $task, string $reminderType)
    {
        $this->task = $task;
        $this->reminderType = $reminderType;
    }

    public function handle()
    {
        Log::info('SendTaskReminderJob started', [
            'task_id'       => $this->task->id,
            'user_id'       => $this->task->user_id,
            'reminder_type' => $this->reminderType,
        ]);

        $user = $this->task->user;
        $tokens = $user->devices()->pluck('token')->toArray();

        if (empty($tokens)) {
            Log::warning("No device tokens found for user {$user->id}");
            return;
        }

        $title = "Task reminder: " . $this->task->title;
        $body = match ($this->reminderType) {
            '3d' => "This task is due in 3 days ({$this->task->due_date})",
            '2d' => "This task is due in 2 days ({$this->task->due_date})",
            default => "This task is due tomorrow ({$this->task->due_date})",
        };

        try {
            $accessToken = $this->getAccessToken();
            $http = new HttpClient();
            $projectId = env('FIREBASE_PROJECT_ID');

            Log::debug("Project ID in reminder job", [$projectId]);

            foreach ($tokens as $token) {
                $payload = [
                    'message' => [
                        'token' => $token,
                        'notification' => [
                            'title' => $title,
                            'body'  => $body,
                        ],
                        'data' => [
                            'task_id'  => (string) $this->task->id,
                            'reminder' => $this->reminderType,
                        ],
                    ],
                ];

                $response = $http->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                    'headers' => [
                        'Authorization' => "Bearer {$accessToken}",
                        'Content-Type'  => 'application/json',
                    ],
                    'json' => $payload,
                ]);

                Log::info('FCM response', [
                    'status' => $response->getStatusCode(),
                    'body'   => (string) $response->getBody(),
                ]);
            }

            $column = "reminder_{$this->reminderType}_sent_at";

            $task = Task::find($this->task->id); // reload fresh model
            $task->forceFill([$column => now()])->save();

            Log::info("Reminder column updated", [
                'task_id' => $task->id,
                $column   => $task->{$column},
            ]);
        } catch (\Throwable $e) {
            Log::error("SendTaskReminderJob failed", [
                'task_id' => $this->task->id,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Get a valid Google OAuth2 access token for FCM
     */
    protected function getAccessToken(): string
    {
        $client = new GoogleClient();
        $client->setAuthConfig(config('services.fcm.credentials') ?? env('GOOGLE_APPLICATION_CREDENTIALS'));
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

        $tokenData = $client->fetchAccessTokenWithAssertion();

        if (isset($tokenData['error'])) {
            throw new \Exception("Failed to fetch access token: " . json_encode($tokenData));
        }

        return $tokenData['access_token'];
    }
}
