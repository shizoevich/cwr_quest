<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PatientDocumentDownloaded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $sharedDocument;
    public $directDownload;

    /**
     * PatientDocumentDownloaded constructor.
     *
     * @param      $sharedDocument
     * @param bool $directDownload
     */
    public function __construct($sharedDocument, $directDownload = false)
    {
        $this->sharedDocument = $sharedDocument;
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
