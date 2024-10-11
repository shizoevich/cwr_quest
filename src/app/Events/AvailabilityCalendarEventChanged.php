<?php

namespace App\Events;

use Carbon\Carbon;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

abstract class AvailabilityCalendarEventChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $providerId;
    protected $startDate;
    
    /**
     * ModelChanged constructor.
     *
     * @param $providerId
     * @param $startDate
     */
    public function __construct($providerId, $startDate)
    {
        $this->providerId = $providerId;
        $this->startDate = $startDate;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return [
            //individual provider's channel
            new PrivateChannel('availabilityFor.' . $this->providerId),
            
            //channel for admin
            new PrivateChannel('availabilityFor'),
        ];
    }
    
    public function broadcastWith()
    {
        return [
            'date' => Carbon::parse($this->startDate)->toDateString(),
        ];
    }

    /**
     * @return string
     */
    public function broadcastAs()
    {
        return 'availability.changed';
    }
}
