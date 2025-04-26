<?php

return [
    'allowed_jobs' => [
        'App\\Jobs\\SampleJobClass' => [
            'process',
        ],
        'App\\Jobs\\AnotherSampleJobClass' => [
            'doFirstTask',
        ],
    ],
    'retry_attempts' => 3,
    'retry_delay' => 5,
];



