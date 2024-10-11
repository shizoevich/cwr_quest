<?php

return [
    'remind_later_gap' => env('TIMESHEET_REMIND_LATER_GAP', 120), // in minutes, 2 hours by default
    'allowed_editing_gap' => env('TIMESHEET_ALLOWED_EDITING_GAP', 18), // 18 hours by default
    'submit_required_gap' => env('TIMESHEET_SUBMIT_REQUIRED_GAP', 36), // 36 hours by default
];
