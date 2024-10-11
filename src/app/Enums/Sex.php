<?php

namespace App\Enums;

class Sex
{
    const MALE = 'M';
    const FEMALE = 'F';
    const UNKNOWN = 'U';

    /**
     * @var array
     */
    public static $list = [
        self::MALE => 'Male',
        self::FEMALE => 'Female',
        self::UNKNOWN => 'Unknown',
    ];
}