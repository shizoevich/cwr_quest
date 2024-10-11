<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PatientDocumentPreview
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $sharedDocument;
    public $directDownload;
    public $uniqueId;

    /**
     * PatientDocumentPreview constructor.
     * @param $sharedDocument
     * @param bool $directDownload
     * @param mixed $uniqueId
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
