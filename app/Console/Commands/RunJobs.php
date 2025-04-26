<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JobQueue;
use Illuminate\Support\Facades\Log;

class RunJobs extends Command
{
    protected $signature = 'jobs:run';

    protected $description = 'Process queued jobs';

    public function handle()
    {
        while (true) {
            $job = JobQueue::where('status', 'pending')
                ->where('run_at', '<=', now())
                ->orderBy('priority', 'desc')
                ->orderBy('run_at', 'asc')
                ->first();

            if (!$job) {
                break; // No more pending jobs
            }

            $this->runJob($job);
        }
    }

    protected function runJob(JobQueue $job)
    {
        Log::info("Processing job: {$job->id}");
        $job->update(['status' => 'running']);

        try {
            if ($job->is_cancelled) {
                $job->update(['status' => 'cancelled']);
                return;
            }

            $instance = app()->make($job->job_class);

            if (!method_exists($instance, $job->job_method)) {
                Log::error("Method {$job->job_method} does not exist in {$job->job_class}");
                $job->update(['status' => 'failed']);
                return;
            }

            // Get the parameters from the job payload
            $params = json_decode($job->payload, true);  // Decode the JSON payload into an array

            // Call the method with the decoded parameters
            call_user_func_array([$instance, $job->job_method], $params);

            $job->update([
                'status' => 'completed',
                'retry_count' => 0,
                'error_log' => null,
            ]);
        } catch (\Throwable $e) {
            Log::error("Job execution failed: {$e->getMessage()}", ['job_id' => $job->id]);
            $job->update(['status' => 'failed', 'error_log' => $e->getMessage()]);
        }
    }

}
