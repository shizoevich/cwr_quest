<?php

namespace Tests\Helpers\OfficeAlly;

use Carbon\Carbon;

class VisitsOfficeAllyHelper
{
    public static function getCellStructureVisitData(): array
    {
        return [
            5 => 'string',
            9 => 'string'
        ];
    }

    public static function getDefaultOptions(): array
    {
        return [
            'full-time' => false,
            'only-visits' => false,
            'month' => null,
            'date' => null,
        ];
    }

    public static function getVisitData(): array
    {
        return [
            'visit_id'  => 123456789,
            'is_paid'   => 0,
            'date'      => '07/13/2023',
            'status' => 'Test Status'
        ];
    }

    public static function getMockVisitData($visitData): array
    {
        return [
            [
                "id" => $visitData['visit_id'],
                "cell" =>
                [
                    $visitData['visit_id'],
                    null,
                    "01/01/1900",
                    "A",
                    null,
                    $visitData['date'],
                    "Gamliel, Dana A",
                    "01/07/1996",
                    "Negin Nasserian, LMFT",
                    $visitData['status'],
                    250.0,
                    250.0,
                    "",
                    "Kaiser Permanente",
                    "",
                    "2|",
                    "895198660|",
                    "0",
                    "0",
                    null,
                    null,
                    1,
                    1
                ]
            ]
        ];
    }

    public static function getDateRange(): array
    {
        $startDate = Carbon::now()->startOfMonth()->subDays(5);
        $endDate = Carbon::now();

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }
}
