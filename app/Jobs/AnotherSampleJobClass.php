<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;

class AnotherSampleJobClass
{
    /**
     * Example method one.
     *
     * @param string $param1
     * @param string $param2
     * @return void
     */
    public function doFirstTask($param1, $param2)
    {
        Log::info("Running doFirstTask with: $param1 and $param2");
        sleep(1); // simulate some work
        Log::info("Finished doFirstTask.");
    }

    /**
     * Example method two.
     *
     * @param string $param1
     * @return void
     */
    public function doSecondTask($param1)
    {
        Log::info("Running doSecondTask with: $param1");
        sleep(1); // simulate some work
        Log::info("Finished doSecondTask.");
    }
}
