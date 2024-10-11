<?php

namespace App\Helpers\StatisticsReport;

interface AbstractStatisticsReportHelper
{
    public static function getData(string $startDate, string $endDate): array;

    public static function getColumnNames(): array;
}