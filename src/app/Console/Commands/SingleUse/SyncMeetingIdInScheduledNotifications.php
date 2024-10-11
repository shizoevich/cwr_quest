<?php

namespace App\Console\Commands\SingleUse;

use App\Models\GoogleMeeting;
use App\Models\ScheduledNotification;
use App\Models\UphealMeeting;
use Illuminate\Console\Command;

class SyncMeetingIdInScheduledNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scheduled-notifications:sync-meeting-id';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        ScheduledNotification::query()
            ->whereNull('sent_at')
            ->whereNull('meeting_id')
            ->orderByDesc('id')
            ->chunk(1000, function ($notifications) {
                foreach ($notifications as $notification) {
                    $serializedNotification = $notification->notification;

                    $meetingId = $this->extractMeetingId($serializedNotification);
                    $meetingType = $this->extractMeetingType($serializedNotification);

                    if ($this->meetingExists($meetingType, $meetingId)) {
                        $notification->update(['meeting_id' => $meetingId, 'meeting_type' => $meetingType]);
                    }
                }
            });
    }

    private function extractMeetingId(string $serializedNotification): ?int
    {
        $matches = null;
        preg_match('/"id";i:(\d+)/', $serializedNotification, $matches);
        return isset($matches[1]) ? (int) $matches[1] : null;
    }

    private function extractMeetingType(string $serializedNotification): ?string
    {
        if (strpos($serializedNotification, 'googleMeeting') !== false) {
            return GoogleMeeting::class;
        }
        if (strpos($serializedNotification, 'uphealMeeting') !== false) {
            return UphealMeeting::class;
        }
        return null;
    }

    private function meetingExists(string $meetingType, int $meetingId): bool
    {
        if (!$meetingType || !$meetingId) {
            return false;
        }
        
        $meetingModel = new $meetingType;
        return $meetingModel->where('id', $meetingId)->exists();
    }
}
