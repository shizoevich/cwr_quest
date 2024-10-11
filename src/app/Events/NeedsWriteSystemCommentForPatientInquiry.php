<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class NeedsWriteSystemCommentForPatientInquiry
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    private $inquiryId;
    private $comment;
    private $adminId;

    /**
     * Create a new event instance.
     *
     * @param $inquiryId
     * @param $comment
     * @param $adminId
     */
    public function __construct($inquiryId, $comment, $adminId = null)
    {
        $this->inquiryId = $inquiryId;
        $this->comment = $comment;
        $this->adminId = $adminId;
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

    /**
     * @return mixed
     */
    public function getInquiryId()
    {
        return $this->inquiryId;
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }


    /**
     * @return mixed
     */
    public function getAdminId()
    {
        return $this->adminId;
    }
}
