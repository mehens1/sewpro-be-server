<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;

class UpdateOverdueTasks extends Command
{
    protected $signature = 'tasks:update-overdue';
    protected $description = 'Mark tasks as overdue if their due date has passed';

    public function handle()
    {
        $count = Task::where('status', 'upcoming')
            ->whereDate('due_date', '<', now())
            ->update(['status' => 'overdue']);

        $this->info("Updated {$count} overdue tasks.");
    }
}
