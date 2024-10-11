<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PatientDocumentUpdate
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public $sharedDocument;
    public $directDownload;
    public $uniqueId;


    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($sharedDocument, $directDownload = false, $uniqueId = null)
    {
        $this->sharedDocument = $sharedDocument;
        $this->directDownload = $directDownload;
        $this->uniqueId       = $uniqueId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
