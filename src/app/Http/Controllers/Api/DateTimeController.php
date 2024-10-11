<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DateTimeController extends Controller {

    public function now() {
        return Carbon::now();
    }

    public function getTime() {
        return $this->now()->format('h:i A');
    }

    public function getTimestamp() {
        return $this->now()->timestamp;
    }

    public function getDateAndTime() {
        $now = $this->now();
        $response = [
            'time' => $now->format('g:i A'),
            'date' => $now->format('m/d/Y'),
        ];
        return $response;
    }

    /**
     * @return array
     */
    public function dateParts() {
        $now = $this->now();
        $response = [
            'year' => $now->year,
            'month' => $now->month,
            'date' => $now->day,
            'hour' => $now->hour,
            'minute' => $now->minute,
            'second' => $now->second,
        ];

        return $response;
    }

}
