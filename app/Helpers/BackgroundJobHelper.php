<?php

if (!function_exists('runBackgroundJob')) {
    /**
     * Run a background job through CLI.
     *
     * @param string $class The class name
     * @param string $method The method to call
     * @param array $params The parameters to pass to the method
     * @return void
     */
    function runBackgroundJob($class, $method, $params = []) {
        $paramsString = implode(',', $params);
        $command = "php " . base_path('run-job.php') . " $class $method \"$paramsString\"";

        // Run the job in the background (Windows and Unix-compatible)
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // For Windows
            pclose(popen($command . ' > NUL 2>&1 &', 'r'));
        } else {
            // For Unix-based systems
            exec($command . ' > /dev/null 2>&1 &');
        }
    }
}
