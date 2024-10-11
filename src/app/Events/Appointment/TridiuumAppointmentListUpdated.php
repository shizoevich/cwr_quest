<?php

namespace App\Events\Appointment;

use App\KaiserAppointment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class TridiuumAppointmentListUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('tridiuum-appointments');
    }
    
    /**
     * @return string
     */
    public function broadcastAs()
    {
        return 'appointments.tridiuum.updated';
    }
    
    public function broadcastWith()
    {
        return [
            'count' => KaiserAppointment::newAppointmentsCount(),
        ];
    }
}
