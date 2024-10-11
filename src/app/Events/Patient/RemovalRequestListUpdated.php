<?php

namespace App\Events\Patient;

use App\Models\Patient\PatientRemovalRequest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RemovalRequestListUpdated implements ShouldBroadcast
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
        return new PrivateChannel('removal-requests');
    }
    
    /**
     * @return string
     */
    public function broadcastAs()
    {
        return 'removal-request.updated';
    }
    
    public function broadcastWith()
    {
        return [
            'count' => PatientRemovalRequest::new()->count(),
        ];
    }
}
