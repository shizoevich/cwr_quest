<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PatientDocumentStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $documentId;
    public $onlyForAdmin;

    /**
     * Create a new event instance.
     *
     * @param $documentId
     * @param $onlyForAdmin
     */
    public function __construct($documentId, $onlyForAdmin)
    {
        $this->documentId = $documentId;
        $this->onlyForAdmin = $onlyForAdmin;
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
