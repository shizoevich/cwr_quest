<?php

namespace App\Services\Patient\DocumentRequest;

use App\Events\NeedsWriteSystemComment;
use App\Jobs\Notifications\RingcentralSms;
use App\Models\Patient\DocumentRequest\PatientDocumentRequest as DocumentRequest;
use App\Models\Patient\DocumentRequest\PatientFormType;
use App\Patient;
use Illuminate\Support\Facades\Mail;
use Twilio\Exceptions\RestException;

class PatientDocumentRequest
{
    /**
     * Patient documentsRequest save
     *
     * @param Patient $patient
     * @param array $data
     * @return DocumentRequest
     */
    public function save(Patient $patient, array $data): DocumentRequest
    {
        while (true) {
            $hash = substr(str_shuffle(md5($patient->id . (string) data_get($data, 'email') . (string) data_get($data, 'phone') . uniqid() . time())), 0, 5);
            if (DocumentRequest::query()->where('hash', $hash)->exists()) {
                continue;
            }
            //create PatientDocumentRequest
            $documentData = [
                'patient_id' => $patient->id,
                'sent_by' => auth()->id(),
                'hash' => $hash,
            ];
            if (isset($data['email'])) {
                $documentData['sent_to_email'] = $data['email'];
            }
            if (isset($data['phone'])) {
                $documentData['sent_to_phone'] = $data['phone'];
            }
            $documentRequest = DocumentRequest::create($documentData);
            break;
        }

        $formsData = [];
        $formTypes = PatientFormType::query()->pluck('id', 'name')->toArray();
        
        foreach ($data['forms'] as $item) {
            if ($item['name'] === 'new_patient') {
                //append agreement for service form
                $formsData[] = [
                    'form_type_id' => $formTypes[DocumentRequest::AGREEMENT_FOR_SERVICE],
                    'metadata' => null,
                ];
                unset($item['metadata']);
            }
            if ($item['name'] === 'payment_for_service') {
                //append attendance policy form
                $formsData[] = [
                    'form_type_id' => $formTypes[DocumentRequest::ATTENDANCE_POLICY],
                    'metadata' => null,
                ];
            }
            $formsData[] = [
                'form_type_id' => $formTypes[$item['name']],
                'metadata' => isset($item['metadata']) ? $item['metadata'] : null,
                'comment' => isset($item['comment']) ? $item['comment']  : null,
            ];
        }

        //create document request items
        if (!empty($formsData)) {
            $documentRequest->items()->createMany($formsData);
        }

        return $documentRequest;
    }

    /**
     * @param DocumentRequest $documentRequest
     */
    public function sendViaEmail(DocumentRequest $documentRequest)
    {
        if (!$documentRequest->sent_to_email) {
            return;
        }
        Mail::to($documentRequest->sent_to_email)->send(new \App\Mail\Patient\DocumentRequest\DocumentRequest($documentRequest, $documentRequest->items, $documentRequest->patient));
        $this->writeDocumentSentComment($documentRequest, $documentRequest->sent_to_email);
    }

    /**
     * @param DocumentRequest $documentRequest
     *
     * @throws RestException
     */
    public function sendViaSms(DocumentRequest $documentRequest)
    {
        if (!$documentRequest->sent_to_phone) {
            return;
        }

        $message = __('messages.document_request', ['url' => url("/f/$documentRequest->hash")]);
        $result = \Bus::dispatchNow(new RingcentralSms($documentRequest->sent_to_phone, $message));

        if (!data_get($result, 'status')) {
            $documentRequest->update(['sent_to_phone_error' => substr((string) data_get($result, 'message'), 0, 255)]);
            throw new RestException(data_get($result, 'message'), 0, 422);
        }

        $this->writeDocumentSentComment($documentRequest, $documentRequest->sent_to_phone);
    }

    /**
     * @param DocumentRequest $documentRequest
     * @param                 $recipient
     */
    private function writeDocumentSentComment(DocumentRequest $documentRequest, $recipient)
    {
        $formTypes = PatientFormType::query()->pluck('title', 'id');
        if (auth()->user()->provider_id) {
            $providerName = auth()->user()->provider->provider_name;
        } else {
            $providerName = auth()->user()->meta->firstname . ' ' . auth()->user()->meta->lastname;
        }
        $comment = trans('comments.documents_sent', [
            'provider' => $providerName,
            'recipient' => $recipient,
            'documents' => '<ul>' . implode('', array_map(function ($docType) use ($formTypes) {
                return '<li>' . $formTypes[$docType] . '</li>';
            }, $documentRequest->items()->pluck('form_type_id')->toArray())) . '</ul>',
        ]);
        event(new NeedsWriteSystemComment($documentRequest->patient->id, $comment));
    }
}
