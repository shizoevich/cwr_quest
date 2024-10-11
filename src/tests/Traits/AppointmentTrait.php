<?php

namespace Tests\Traits;

use App\Appointment;

trait AppointmentTrait
{
    public function generateAppointment(array $data = [])
    {
        return factory(Appointment::class)->create($data);
    }
}