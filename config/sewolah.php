<?php

return [
    /*
    | Minimum number of working days (Monday–Friday) between the day a
    | customer submits a booking request and the vehicle pickup date.
    | Last-minute bookings are not accepted.
    */
    'min_working_days' => (int) env('SEWOLAH_MIN_WORKING_DAYS', 3),
];
