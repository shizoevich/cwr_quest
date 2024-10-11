<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PatientDocumentUpdateStatus
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $updatedDocument;
    public $directDownload;

    /**
     * PatientDocumentUpdateStatus constructor.
     *
     * @param      $updatedDocument
     * @param bool $directDownload
     */
    public function __construct($updatedDocument, $directDownload = false)
    {
        $this->updatedDocument = $updatedDocument;
        $this->directDownload = $directDownload;
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
