<?php

namespace App\Enums;

class VisitType
{
    const VIRTUAL = 'virtual';
    const IN_PERSON = 'in_person';

    /**
     * @var array
     */
    public static $list = [
        self::VIRTUAL,
        self::IN_PERSON,
    ];
}