<?php

namespace App\Jobs\Google\CalendarEvent;

use App\Helpers\Google\ReportService;
use App\Models\GoogleMeeting;
use App\Models\GoogleMeetingCallLog;
use App\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GetConferenceCallLogs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    /**
     * @var Carbon
     */
    private $startDate;
    /**
     * @var Carbon|null
     */
    private $endDate;
    
    /**
     * GetConferenceCallLogs constructor.
     *
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     */
    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate ?? Carbon::now()->subDay();
        $this->endDate = $endDate;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
       $service = (new ReportService())->getService();
       $nextPageToken = null;
       do {
           $activities = $service->activities->listActivities('all', 'meet', $this->getOptParams($nextPageToken));
           $nextPageToken = $activities->getNextPageToken();
           if(!empty($activities->getItems())){
            foreach ($activities->getItems() as $item) {
                $this->processingActivityRecord($item); 
            }
           }
       } while($nextPageToken);
    }
    
    /**
     * @param \Google_Service_Reports_Activity $item
     */
    private function processingActivityRecord(\Google_Service_Reports_Activity $item)
    {
        $id = $item->getId();
        $externalId = $id->getUniqueQualifier();
    
        /**
         * Skip if already exists in DB
         */
        if(GoogleMeetingCallLog::query()->where('external_id', $externalId)->exists()) {
            return;
        }
        
        $callEndsAt = $id->getTime();
        $callEndsAt = Carbon::parse($callEndsAt)->timezone(config('app.timezone'));
        $providerId = $this->getProviderId($item->getActor());
        
        foreach($item->getEvents() as $event) {
            $identifier = null;
            $eventData = [
                'google_meeting_id' => null,
                'provider_id' => $providerId,
                'external_id' => $externalId,
                'duration' => null,
                'is_external' => null,
                'caller_name' => null,
                'ip' => null,
                'call_starts_at' => null,
                'call_ends_at' => $callEndsAt->toDateTimeString(),
            ];
            
            /** @var \Google_Service_Reports_ActivityEvents $event */
            foreach ($event->getParameters() as $param) {
                /** @var \Google_Service_Reports_ActivityEventsParameters $param */
                switch ($param->getName()) {
                    case 'is_external':
                        $eventData['is_external'] = $param->getBoolValue();
                        break;
                    case 'conference_id':
                        /**
                         * Conference id from audit response != conference id
                         * from event response (and != conference id from DB)
                         */
//                        $googleMeeting = GoogleMeeting::query()
//                            ->where('conference_external_id', $param->getValue())
//                            ->first();
//                        $eventData['google_meeting_id'] = optional($googleMeeting)->getKey();
                        break;
                    case 'calendar_event_id':
                        $googleMeeting = GoogleMeeting::query()
                            ->where('calendar_event_external_id', $param->getValue())
                            ->first();
                        $eventData['google_meeting_id'] = optional($googleMeeting)->getKey();
                        break;
                    case 'display_name':
                        $eventData['caller_name'] = $param->getValue();
                        break;
                    case 'ip_address':
                        $eventData['ip'] = $param->getValue();
                        break;
                    case 'duration_seconds':
                        $eventData['duration'] = (int)$param->getIntValue();
                        $eventData['call_starts_at'] = $callEndsAt->copy()->subSeconds($eventData['duration'])->toDateTimeString();
                        break;
                    case 'identifier':
                        $identifier = $param->getValue();
                        break;
                }
            }
            if(empty($eventData['caller_name'])) {
                $eventData['caller_name'] = $identifier;
            }
            if($eventData['google_meeting_id'] && $eventData['duration'] !== null && $eventData['external_id'] !== null && $eventData['is_external'] !== null) {
                GoogleMeetingCallLog::create($eventData);
            }
        }
    }
    
    /**
     * @param \Google_Service_Reports_ActivityActor|null $actor
     *
     * @return int|null
     */
    private function getProviderId($actor)
    {
        if(!$actor || $actor->getCallerType() !== 'USER') {
            return null;
        }
        $profileId = $actor->getProfileId();
        $user = User::query()->withTrashed()->where('google_id', $profileId)->first();
        if(!$user) {
            $allowedEmailDomains = ['changewithinreach.care', 'cwr.care'];
            $emails = [];
            $email = $actor->getEmail();
            if($email) {
                $emails[] = $email;
                $emailParts = explode('@', $email);
                $emailDomain = $emailParts[1];
                foreach ($allowedEmailDomains as $allowedDomain) {
                    if($emailDomain !== $allowedDomain) {
                        $emails[] = $emailParts[0] . '@' . $allowedDomain;
                    }
                }
                $user = User::query()->withTrashed()->whereIn('email', $emails)->first();
            }
        }
        
        return optional($user)->provider_id;
    }
    
    /**
     * @param $nextPageToken
     *
     * @return array
     */
    private function getOptParams($nextPageToken)
    {
        /**
         * @see https://developers.google.com/admin-sdk/reports/v1/reference/activities/list
         */
        $params = [
            'startTime' => $this->startDate->toRfc3339String(),
            'maxResults' => 1000,
            'eventName' => 'call_ended',
        ];
        if($this->endDate) {
            $params['endTime'] = $this->endDate->toRfc3339String();
        }
        if($nextPageToken) {
            $params['pageToken'] = $nextPageToken;
        }
        
        return $params;
    }
}
