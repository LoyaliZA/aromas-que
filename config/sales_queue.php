<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Queue priority for per-visit disability flag
    |--------------------------------------------------------------------------
    |
    | Lower values are served first. Applied when reception checks
    | "Presenta Discapacidad" on enqueue (has_disability = true).
    |
    */
    'disability_queue_priority' => (int) env('QUEUE_DISABILITY_PRIORITY', 15),
];
