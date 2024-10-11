<?php

namespace App\Enums;

class PatientPreferredPhone
{
    const HOME_PHONE = 1;
    const WORK_PHONE = 2;
    const CELL_PHONE = 3;
    const DO_NOT_CALL = 4;

    public static $list = [
        self::HOME_PHONE => 'H',
        self::CELL_PHONE => 'C',
        self::WORK_PHONE => 'W',
        self::DO_NOT_CALL => 'D',
    ];
}