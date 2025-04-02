<?php

// Requirements:
// - Doctor’s appointments do not overlap.

// Pre-define doctors and their schedule as a config
// Could turn this into DB tables if have UI interface to add/edit these

return [
    1 => [
        'name'  => 'Harry Potter',
        'email' => 'harry.potter@example.com',
        'schedules' => [
          [
            'day_of_week' => 'Sunday',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
          ],
          [
            'day_of_week' => 'Sunday',
            'start_time' => '13:00:00',
            'end_time' => '17:00:00',
          ],
          [
            'day_of_week' => 'Monday',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
          ],
          [
            'day_of_week' => 'Monday',
            'start_time' => '13:00:00',
            'end_time' => '17:00:00',
          ]
        ]
    ],
    2 => [
        'name'  => 'Hermione Granger',
        'email' => 'hermione.granger@example.com',
        'schedules' => [
          [
            'day_of_week' => 'Wednesday',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
          ],
          [
            'day_of_week' => 'Thursday',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
          ],
          [
            'day_of_week' => 'Friday',
            'start_time' => '12:00:00',
            'end_time' => '17:00:00',
          ]
        ]
    ],
    3 => [
        'name'  => 'Ron Weasley',
        'email' => 'ron.weasley@example.com',
        'schedules' => [
          [
            'day_of_week' => 'Wednesday',
            'start_time' => '12:00:00',
            'end_time' => '17:00:00',
          ],
          [
            'day_of_week' => 'Thursday',
            'start_time' => '12:00:00',
            'end_time' => '17:00:00',
          ],
          [
            'day_of_week' => 'Friday',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
          ]
        ]
    ],
    4 => [
        'name'  => 'Draco Malfoy',
        'email' => 'draco.malfoy@example.com',
        'schedules' => [
          [
            'day_of_week' => 'Saturday',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
          ],
          [
            'day_of_week' => 'Saturday',
            'start_time' => '13:00:00',
            'end_time' => '17:00:00',
          ]
        ]
    ],
];
