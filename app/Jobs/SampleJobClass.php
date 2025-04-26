<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;

class SampleJobClass
{
    protected $param1;
    protected $param2;

    /**
     * Create a new instance.
     *
     * @param string $param1
     * @param string $param2
     * @return void
     */
    public function __construct($param1, $param2)
    {
        $this->param1 = $param1;
        $this->param2 = $param2;
    }

    /**
     * Execute the task.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('SampleJobClass handle started');

        $this->process($this->param1, $this->param2);

        Log::info('SampleJobClass handle completed');
    }

    /**
     * Process the task.
     *
     * @param string $param1
     * @param string $param2
     * @return void
     */
    public function process($param1, $param2)
    {
        Log::info("Processing job with parameters: $param1, $param2");

        sleep(3); // Simulate a long-running task

        Log::info("Finished processing job with parameters: $param1, $param2");
    }
}
