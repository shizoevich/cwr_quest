<?php

namespace App\Helpers\Sites\OfficeAlly\Enums;

/**
 * Class AppointmentStatuses
 * @package App\Helpers\Sites\OfficeAlly\Enums
 */
class AppointmentStatuses
{
    const ACTIVE = 320;
    const CANCELLED_BY_PATIENT = 321;
    const CANCELLED_BY_OFFICE = 322;
    const PATIENT_DID_NOT_COME = 323;
    const CANCELLED_BY_PROVIDER = 324;
    const COMPLETED = 325;
    const VISIT_CREATED = 326;
    const RESCHEDULED = 327;
    const CONFIRMED = 328;
    const LAST_MINUTE_CANCEL_BY_PATIENT = 329;
    const LAST_MINUTE_RESCHEDULE = 330;
    const IN_ROOM = 331;
    const CHECKED_IN = 332;
    const CHECKED_OUT = 333;
    const LEFT_MESSAGE = 361;
}