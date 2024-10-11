<?php

namespace App\Events\Ringcentral;

use App\Models\RingcentralCallLog;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RingcentralCallChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    /**
     * @var RingcentralCallLog
     */
    private $callLog;
    
    public $queue = 'ringout-socket';
    
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(RingcentralCallLog $callLog)
    {
        $this->callLog = $callLog;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel(sprintf('users.%s.ring-out.%s', $this->callLog->user_id, $this->callLog->id));
    }
    
    /**
     * @return string
     */
    public function broadcastAs()
    {
        return 'ring-out.call.updated';
    }
    
    public function broadcastWith()
    {
        return [
            'call_log' => $this->callLog->toArray(),
        ];
    }
}
