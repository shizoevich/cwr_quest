<?php

namespace App\Jobs\Google\CalendarEvent;

use App\Helpers\Google\CalendarService;
use Google_Service_Calendar_Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteCalendarEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string
     */
    private $eventId;
    
    /**
     * DeleteCalendarEvent constructor.
     *
     * @param string $eventId
     */
    public function __construct(string $eventId)
    {
        $this->eventId = $eventId;
    }
    
    public function handle()
    {
        $service = (new CalendarService())->getService();
        
        return $service->events->delete(
            config('services.google.calendar_id'),
            $this->eventId
        );
    }
}