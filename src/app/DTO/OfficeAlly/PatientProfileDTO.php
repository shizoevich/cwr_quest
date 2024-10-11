<?php

namespace App\DTO\OfficeAlly;

use Spatie\DataTransferObject\DataTransferObject;

class PatientProfileDTO extends DataTransferObject
{
    /** @var string|null|int */
    public $position;

    /** @var string|null */
    public $pos;

    /** @var string|null */
    public $cpt;

    /** @var string|null */
    public $modifier_a;

    /** @var string|null */
    public $modifier_b;

    /** @var string|null */
    public $modifier_c;

    /** @var string|null */
    public $modifier_d;

    /** @var string|null */
    public $diagnose_pointer;

    /** @var float|null */
    public $charge;

    /** @var int|null */
    public $days_or_units;
}
