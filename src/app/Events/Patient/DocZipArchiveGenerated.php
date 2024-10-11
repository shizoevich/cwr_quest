<?php

namespace App\Events\Patient;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class DocZipArchiveGenerated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $documentZipArchive;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($documentZipArchive)
    {
        $this->documentZipArchive = $documentZipArchive;
    }
    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel(sprintf('zip-archive.%s', $this->documentZipArchive->user_id));
    }

    public function broadcastAs()
    {
        return 'zip-archive.created'; 
    }

    public function broadcastWith()
    {
        return [
            'generated_zip_archive' => $this->documentZipArchive->toArray(),
        ];
    }
}
