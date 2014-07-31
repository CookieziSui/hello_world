<?php

$LEVEL = array(
    'L4' => 39,
    'L5' => 25,
    'L6' => 26,
    'L7' => 13,
    'L8' => 4,
    'I2' => 3,
);

$VALID_DAILY_EFFORT = array(
    'INTERNAL' => 8,
    'EXTERNAL' => 6.5
);

$RESIGN_REASON = array(
    '0'  => 'Payment',
    '1'  => 'Career path',
    '2'  => 'Challenging from Work',
    '3'  => 'Relationship with workmate',
    '4'  => 'Respect & Acknowledgement',
    '5'  => 'Home & Work',
    '6'  => 'Personal Capability',
    '7'  => 'Family',
    '8'  => 'Health',
    '9'  => 'Back to College',
    '10' => 'Penalty from Rule Break',
    '11' => 'Bad Performance'
);

/*
 * Criteria for task schedule analysis
 */
$TASK_SCHEDULE_CRITERIA = array(
    0 => array(
        'PERCENT_START' => 0,
        'PERCENT_END'   => 1.0,
        'SCORE_MIN'     => 5.0,
        'SCORE_MAX'     => 5.0
    ),
    1 => array(
        'PERCENT_START' => 1.1,
        'PERCENT_END'   => 5.0,
        'SCORE_MIN'     => 4.0,
        'SCORE_MAX'     => 4.9
    ),
    2 => array(
        'PERCENT_START' => 5.1,
        'PERCENT_END'   => 10.0,
        'SCORE_MIN'     => 3.0,
        'SCORE_MAX'     => 3.9
    ),
    3 => array(
        'PERCENT_START' => 10.1,
        'PERCENT_END'   => 20.0,
        'SCORE_MIN'     => 0,
        'SCORE_MAX'     => 2.9
    ),
    4 => array(
        'PERCENT_START' => 20.1,
        'PERCENT_END'   => 9999.9,
        'SCORE_MIN'     => 0,
        'SCORE_MAX'     => 0
    ),
    //5 => array(
    //    'PERCENT_START' => NULL,
    //    'PERCENT_END'   => NULL,
    //    'SCORE_MIN'     => 0,
    //    'SCORE_MAX'     => 0
    //)
);

/*
 * Criteria for bug analysis
 */
$BUG_CRITERIA = array(
    'queryTObug' => array(
        0 => array(
            'PERCENT_START' => 80,
            'PERCENT_END'   => 100,
            'SCORE_MIN'     => 5.0,
            'SCORE_MAX'     => 5.0
        ),
        1 => array(
            'PERCENT_START' => 55,
            'PERCENT_END'   => 79,
            'SCORE_MIN'     => 4.0,
            'SCORE_MAX'     => 4.9
        ),
        2 => array(
            'PERCENT_START' => 30,
            'PERCENT_END'   => 54,
            'SCORE_MIN'     => 3.0,
            'SCORE_MAX'     => 3.9
        ),
        3 => array(
            'PERCENT_START' => 0,
            'PERCENT_END'   => 29,
            'SCORE_MIN'     => 0,
            'SCORE_MAX'     => 2.9
        )
    ),
    'query_life' => array(
        0 => array(
            'DURATION_START' => 0,
            'DURATION_END'   => 24.0,
            'SCORE_MIN'      => 5.0,
            'SCORE_MAX'      => 5.0
        ),
        1 => array(
            'DURATION_START' => 24.1,
            'DURATION_END'   => 36.0,
            'SCORE_MIN'      => 4.0,
            'SCORE_MAX'      => 4.9
        ),
        2 => array(
            'DURATION_START' => 36.1,
            'DURATION_END'   => 48.0,
            'SCORE_MIN'      => 3.0,
            'SCORE_MAX'      => 3.9
        ),
        3 => array(
            'DURATION_START' => 48.1,
            'DURATION_END'   => 60.0,
            'SCORE_MIN'      => 0,
            'SCORE_MAX'      => 2.9
        ),
        4 => array(
            'DURATION_START' => 60.1,
            'DURATION_END'   => 9999.9,
            'SCORE_MIN'      => 0,
            'SCORE_MAX'      => 0
        )
    ),
    'valid_bug' => array(
        0 => array(
            'PERCENT_START' => 95,
            'PERCENT_END'   => 100,
            'SCORE_MIN'     => 5.0,
            'SCORE_MAX'     => 5.0
        ),
        1 => array(
            'PERCENT_START' => 84,
            'PERCENT_END'   => 94,
            'SCORE_MIN'     => 4.0,
            'SCORE_MAX'     => 4.9
        ),
        2 => array(
            'PERCENT_START' => 75,
            'PERCENT_END'   => 84,
            'SCORE_MIN'     => 3.0,
            'SCORE_MAX'     => 3.9
        ),
        3 => array(
            'PERCENT_START' => 0,
            'PERCENT_END'   => 74,
            'SCORE_MIN'     => 0,
            'SCORE_MAX'     => 2.9
        )
    ),
    'bug_life' => array(
        0 => array(
            'DURATION_START' => 0,
            'DURATION_END'   => 48.0,
            'SCORE_MIN'      => 5.0,
            'SCORE_MAX'      => 5.0
        ),
        1 => array(
            'DURATION_START' => 48.1,
            'DURATION_END'   => 60.0,
            'SCORE_MIN'      => 4.0,
            'SCORE_MAX'      => 4.9
        ),
        2 => array(
            'DURATION_START' => 60.1,
            'DURATION_END'   => 72.0,
            'SCORE_MIN'      => 3.0,
            'SCORE_MAX'      => 3.9
        ),
        3 => array(
            'DURATION_START' => 72.1,
            'DURATION_END'   => 84.0,
            'SCORE_MIN'      => 0,
            'SCORE_MAX'      => 2.9
        ),
        4 => array(
            'DURATION_START' => 84.1,
            'DURATION_END'   => 9999.9,
            'SCORE_MIN'      => 0,
            'SCORE_MAX'      => 0
        )
    )
);

$BUG_ANALYSIS_CATEGORY = array(
    '0' => 'By Category',
    '1' => 'By Status',
    '2' => 'By Severity',
    '3' => 'By Priority',
    '4' => 'By Reportor',
    '5' => 'By Open Per Day',
    '6' => 'By Close Per Day'    
);
?>
