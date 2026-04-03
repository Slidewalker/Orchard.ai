<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Winnowing Thresholds
    |--------------------------------------------------------------------------
    |
    | Defines the score above which content is considered "Wheat" (Value).
    |
    */
    'thresholds' => [
        'wheat_score' => 0.7,
        'chaff_score' => 0.7,
    ],
    
    'scoring_weights' => [
        'utility' => 0.5,
        'privacy' => 0.3,
        'sustainability' => 0.2,
    ],
];
