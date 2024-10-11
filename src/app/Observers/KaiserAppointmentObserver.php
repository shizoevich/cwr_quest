<?php

namespace App\Observers;

use App\Events\Appointment\TridiuumAppointmentListUpdated;
use App\KaiserAppointment;

class KaiserAppointmentObserver
{
    /**
     * @param KaiserAppointment $kaiserAppointment
     */
    public function updated(KaiserAppointment $kaiserAppointment)
    {
       if($kaiserAppointment->isDirty('status')) {
           event(new TridiuumAppointmentListUpdated());
       }
    }

    /**
     * @param KaiserAppointment $kaiserAppointment
     */
    public function deleted(KaiserAppointment $kaiserAppointment)
    {
        event(new TridiuumAppointmentListUpdated());
    }

    /**
     * @param KaiserAppointment $kaiserAppointment
     */
    public function created(KaiserAppointment $kaiserAppointment)
    {
        event(new TridiuumAppointmentListUpdated());
    }

    /**
     * @param KaiserAppointment $kaiserAppointment
     */
    public function restored(KaiserAppointment $kaiserAppointment)
    {
        event(new TridiuumAppointmentListUpdated());
    }
}