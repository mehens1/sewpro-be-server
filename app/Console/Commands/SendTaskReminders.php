<?php

namespace App\Console\Commands;

use App\Jobs\SendTaskReminderJob;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendTaskReminders extends Command
{
    protected $signature = 'tasks:send-reminders';
    protected $description = 'Send task reminders 3 days and 1 day before due date';
    
    public function handle()
    {
        $tolerance = 1;

        foreach ([3 => '3d', 2 => '2d', 1 => '1d'] as $days => $type) {
            $start = Carbon::now()->addDays($days)->subMinutes($tolerance);
            $end   = Carbon::now()->addDays($days)->addMinutes($tolerance);

            $tasks = Task::where('status', '!=', 'done')
                ->whereNull('reminder_' . $type . '_sent_at')
                ->whereBetween('due_date', [$start, $end])
                ->get();

            foreach ($tasks as $task) {
                SendTaskReminderJob::dispatch($task, $type);
            }
        }

        return 0;
    }
}
