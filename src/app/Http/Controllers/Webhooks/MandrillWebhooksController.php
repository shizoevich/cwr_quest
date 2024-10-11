<?php

namespace App\Http\Controllers\Webhooks;

use App\Events\PatientDocumentUpdateStatus;
use App\Http\Controllers\Controller;
use App\Jobs\Database\UpdateDocumentSendInfoDropped;
use App\Jobs\Database\UpdateDocumentSendInfoOpened;
use App\Jobs\Database\UpdateDocumentSendInfoSent;
use App\Models\Patient\DocumentRequest\PatientDocumentRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MandrillWebhooksController extends Controller
{
    public function index(Request $request)
    {
        if (isset($request->all()['mandrill_events'])) {
            $events = \GuzzleHttp\json_decode($request->all()['mandrill_events']);

            if (($events !== null) && (count($events) > 0) && is_array($events)) {
                $sentArray = array_filter($events, function ($event) {
                    return $event->event == 'send';
                });

                ///////////////////////////code for handling Mandrill events related to document requests.////////////////////////////////
                //Log::info($sentArray);
                array_map(function($record) {
                    if ($record->msg->subject =="Re: Upcoming Appointment Documentation Request" || $record->msg->subject == "This an example webhook message") {
                        // $logEvenetArray = [];
                        // $logEvenetArray['subject'] = $record->msg->subject;
                        //Log::info($logEvenetArray);
                        $documentRequest = PatientDocumentRequest::where('sent_to_email', $record->msg->email)
                            ->orderBy('created_at', 'desc')
                            ->first();
                        
                        if (isset($documentRequest)) {
                            $documentRequest->update([
                                'mandrill_event_id' => $record->_id, 
                                'sent_at' => Carbon::now(),
                            ]);
                        }
                    }
                }, $sentArray);

                ///////////////////////////code for handling Mandrill events related to document requests.////////////////////////////////

                $droppedArray = array_filter($events, function ($event) {
                    return $event->event == 'reject' || $event->event == 'soft_bounce' || $event->event == 'hard_bounce';
                });

                $openedArray = array_filter($events, function ($event) {
                    return $event->event == 'open';
                });

                array_walk($droppedArray, function ($record) {
                    $updatedDocument = $this->dispatchNow(new UpdateDocumentSendInfoDropped($record->_id));
                    if ($updatedDocument) {
                        event(new PatientDocumentUpdateStatus($updatedDocument));
                    }
                });

                array_walk($sentArray, function ($record) {

                    $this->dispatchNow(new UpdateDocumentSendInfoSent($record->_id));
                });

                array_walk($openedArray, function ($record) {
                    $updatedDocument = $this->dispatchNow(new UpdateDocumentSendInfoOpened($record->_id, $record->ts));

                    if ($updatedDocument) {
                        event(new PatientDocumentUpdateStatus($updatedDocument));
                    }
                });

                //code for handling Mandrill events related to document requests.
                $eventTypes = array(
                    array('type' => 'deferral', 'field' => 'deferral_at'),
                    array('type' => 'hard-bounced', 'field' => 'hard_bounced_at'),
                    array('type' => 'soft-bounced', 'field' => 'soft_bounced_at'),
                    array('type' => 'bounced', 'field' => 'bounced_at'),
                    array('type' => 'delivered', 'field' => 'delivered_at'),
                    array('type' => 'click', 'field' => 'click_at'),
                    array('type' => 'spam', 'field' => 'spam_at'),
                    array('type' => 'unsub', 'field' => 'unsub_at'),
                    array('type' => 'rejected', 'field' => 'rejected_at'),
                );

                foreach ($eventTypes as $event) {
                    $this->processEvents($events, $event['type'], $event['field']);
                }
            }
        } else {
            return 'status';
        }
    }

    private function processEvents($events, $eventType, $fieldName)
    {
        //code for handling Mandrill events related to document requests.
        $eventArray = array_filter($events, function ($event) use ($eventType) {
            return $event->event == $eventType;
        });

        foreach ($eventArray as $record) {
            if (isset($record->_id)) {
                PatientDocumentRequest::where('mandrill_event_id', $record->_id)
                    ->each(function (PatientDocumentRequest $request) use ($fieldName) {
                        $request->update([$fieldName => Carbon::now()]);
                    });
            }   
        }
    }
}
