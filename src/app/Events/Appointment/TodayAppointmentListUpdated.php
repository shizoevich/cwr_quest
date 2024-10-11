<?php

namespace App\Events\Appointment;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class TodayAppointmentListUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    /**
     * @var array
     */
    private $providerIds;
    
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(array $providerIds)
    {
        $this->providerIds = array_unique($providerIds);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        $channels = [new PrivateChannel('appointments')];
        foreach ($this->providerIds as $providerId) {
            $channels[] = new PrivateChannel(sprintf('providers.%s.appointments', $providerId));
        }
        
        return $channels;
    }
    
    /**
     * @return string
     */
    public function broadcastAs()
    {
        return 'appointments.today.updated';
    }
}
