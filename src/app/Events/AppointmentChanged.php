<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class AppointmentChanged extends AvailabilityCalendarEventChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
}
