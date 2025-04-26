<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JobController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/jobs', [JobController::class, 'showAvailableJobs'])->name('jobs.index');
Route::post('/jobs/dispatch', [JobController::class, 'dispatchJob'])->name('jobs.dispatch');
Route::get('/jobs/{id}/status', [JobController::class, 'getJobStatus']);
Route::patch('/jobs/{job}/cancel', [JobController::class, 'cancel'])->name('jobs.cancel');



// Route::get(
//     '/jobs', 
//     [JobController::class, 'index'])->name('jobs.index');
// Route::post(
//     '/jobs/retry/{jobId}', 
//     [JobController::class, 'retry'])->name('jobs.retry');
// Route::post(
//     '/jobs/cancel/{jobId}', 
//     [JobController::class, 'cancel'])->name('jobs.cancel');
// Route::post(
//     '/jobs/dispatch-delayed', 
//     [JobController::class, 'dispatchDelayed'])->name('jobs.dispatchDelayed');
// Route::post(
//     '/jobs/dispatch-priority', 
//     [JobController::class, 'dispatchPriority'])->name('jobs.dispatchPriority');
