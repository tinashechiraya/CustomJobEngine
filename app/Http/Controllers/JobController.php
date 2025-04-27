<?php

namespace App\Http\Controllers;

use App\Models\JobQueue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use ReflectionClass;
use Symfony\Component\Process\Exception\ProcessFailedException;


class JobController extends Controller
{
    public function showAvailableJobs()
    {
        $jobsPath = app_path('Jobs');
        $jobClasses = [];

        foreach (File::allFiles($jobsPath) as $file) {
            $className = 'App\\Jobs\\' . str_replace('.php', '', $file->getFilename());

            if (class_exists($className)) {
                $reflection = new \ReflectionClass($className);

                $methods = collect($reflection->getMethods(\ReflectionMethod::IS_PUBLIC))
                    ->reject(fn($method) => $method->class != $reflection->getName() || $method->isConstructor())
                    ->map(function ($method) {
                        return [
                            'name' => $method->getName(),
                            'parameters' => collect($method->getParameters())->map(function ($param) {
                                return $param->getName();
                            })->toArray(),
                        ];
                    })
                    ->toArray();

                $jobClasses[] = [
                    'class' => $className,
                    'methods' => $methods,
                ];
            }
        }

        $queuedJobs = JobQueue::orderBy('created_at', 'desc')->simplePaginate(5);

        return view('jobs.index', compact('jobClasses', 'queuedJobs'));
    }


    public function dispatchJob(Request $request)
    {
        $request->validate([
            'class' => 'required|string',
            'method' => 'required|string',
            'delay' => 'nullable|integer|min:0',
            'priority' => 'nullable|integer',
            'params' => 'array'
        ]);

        $delaySeconds = (int) $request->input('delay', 0);
        $priority = $request->input('priority', 0);
        $params = $request->input('params', []);

        $job = JobQueue::create([
            'job_class' => $request->class,
            'job_method' => $request->method,
            'payload' => json_encode($params),
            'status' => 'pending',
            'run_at' => now()->addSeconds($delaySeconds),
            'priority' => $priority,
        ]);

        $this->startBackgroundJobRunner();

        return redirect()->route('jobs.index')->with('success', 'Job queued successfully!');
    }

    protected function startBackgroundJobRunner()
    {
        $command = 'nohup php artisan job:run > /dev/null 2>&1 &';
        $process = Process::fromShellCommandline($command);
        $process->setTimeout(null);
        $process->disableOutput();
        $process->run();
    }


    public function getJobStatus($id)
    {
        $job = JobQueue::find($id);

        if ($job) {
            return response()->json([
                'status' => $job->status,
                'retry_count' => $job->retry_count,
            ]);
        }

        return response()->json(['error' => 'Job not found'], 404);
    }

    public function cancel(JobQueue $job)
    {
        if ($job->status === 'running') {
            $job->update([
                'is_cancelled' => true,
                'status' => 'cancelled',
            ]);
            return redirect()->route('jobs.index')->with('success', 'Job cancelled successfully.');
        }

        return redirect()->route('jobs.index')->with('success', 'Job is not running.');
    }
}