<?php

require __DIR__ . '/vendor/autoload.php';

if (php_sapi_name() != 'cli') {
    die("This script should only be run from the command line.");
}

// Bootstrap the Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';

// Ensure the app is bootstrapped fully
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Access Laravel's service container
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$config = require __DIR__ . '/config/background-jobs.php';


// Get input arguments
$className = $argv[1] ?? null;
$methodName = $argv[2] ?? null;
$params = isset($argv[3]) ? explode(',', $argv[3]) : [];

try {

    if (!class_exists($className)) {
        throw new Exception("Class $className does not exist.");
    }

    if (!isset($config['allowed_jobs'][$className])) {
        throw new Exception("Execution of class $className is not allowed.");
    }

    $classInstance = new $className(...$params);

    if (!method_exists($classInstance, $methodName)) {
        throw new Exception("Method $methodName does not exist in class $className.");
    }

    if (!in_array($methodName, $config['allowed_jobs'][$className])) {
        throw new Exception("Execution of method $methodName in class $className is not allowed.");
    }

    executeJobWithRetry($className, $classInstance, $methodName, $params, $config);

} catch (Exception $e) {
    logJobError($e);
}


/**
 * Execute the job with retry logic.
 *
 * @param string $className The class name
 * @param object $classInstance The class instance
 * @param string $methodName The method to call
 * @param array $params The parameters to pass to the method
 * @return void
 */
function executeJobWithRetry($className, $classInstance, $methodName, $params, $config)
{
    $maxAttempts = $config['retry_attempts'];
    $retryDelay = $config['retry_delay'];
    $attempts = 0;

    while ($attempts < $maxAttempts) {
        try {
            $attempts++;
            $result = call_user_func_array([$classInstance, $methodName], $params);
            logJobExecution($className, $methodName, 'success');
            break;
        } catch (Exception $e) {
            if ($attempts < $maxAttempts) {
                logJobExecution($className, $methodName, 'retry', $e->getMessage());
                sleep($retryDelay);
            } else {
                logJobExecution($className, $methodName, 'failure', $e->getMessage());
            }
        }
    }
}


/**
 * Log the job execution details.
 *
 * @param string $className The class name
 * @param string $methodName The method name
 * @param string $status The job status (success, retry, failure)
 * @param string|null $error The error message if failed
 * @return void
 */
function logJobExecution($className, $methodName, $status, $error = null)
{
    $logFile = __DIR__ . '/storage/logs/background_jobs.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] Class: $className, Method: $methodName, Status: $status";
    if ($status === 'failure' || $status === 'retry') {
        $logMessage .= ", Error: $error";
    }
    file_put_contents($logFile, $logMessage . PHP_EOL, FILE_APPEND);
}


/**
 * Log the full exception to errors log.
 */
function logJobError(Exception $e)
{
    $errorLogFile = __DIR__ . '/storage/logs/background_jobs_errors.log';
    $timestamp = date('Y-m-d H:i:s');
    $errorMessage = "[$timestamp] Exception: " . $e->getMessage() .
                    " in " . $e->getFile() . " on line " . $e->getLine() .
                    "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    file_put_contents($errorLogFile, $errorMessage . PHP_EOL, FILE_APPEND);
}