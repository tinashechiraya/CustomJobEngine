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