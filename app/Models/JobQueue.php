<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobQueue extends Model
{
    protected $table = 'job_queue';

    protected $fillable = [
        'job_class',
        'job_method',
        'payload',
        'status',
        'priority',
        'run_at',
        'retry_count',
        'error_message',
    ];
}
