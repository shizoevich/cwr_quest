<?php

namespace Tests\Traits;

use App\KaiserAppointment;

trait KaiserAppointmentTrait
{
    public function generateKaiserAppointment(array $data = [])
    {
        return factory(KaiserAppointment::class)->create($data);
    }
}