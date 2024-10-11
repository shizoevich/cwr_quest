<?php

namespace App\Services\Twilio;

use App\Patient;
use App\TwilioSubscribe;

class TwilioSmsService
{
    private const SUBSCRIBE_STATUSES = [
        'START',
        'YES',
        'UNSTOP',
    ];

    private const UNSUBSCRIBE_STATUSES = [
        'STOP',
        'UNSUBSCRIBE',
        'CANCEL',
    ];

    public function processSms(array $smsData): void
    {
        if (array_key_exists('OptOutType', $smsData)) {
            if (in_array($smsData['OptOutType'], self::SUBSCRIBE_STATUSES, true)) {
                $this->processSubscribe($smsData);
            } else if (in_array($smsData['OptOutType'], self::UNSUBSCRIBE_STATUSES, true)) {
                $this->processUnsubscribe($smsData);
            }
        }
    }

    private function processSubscribe(array $smsData): void
    {
        $patient = $this->getPatient($smsData['From']);

        TwilioSubscribe::updateOrCreate(
            [
                'patient_id' => $patient->getKey()
            ],
            [
                'phone' => $patient->cell_phone,
                'status' => TwilioSubscribe::SUBSCRIBE,
            ]
        );
    }
    
    private function processUnsubscribe(array $smsData): void
    {
        $patient = $this->getPatient($smsData['From']);

        TwilioSubscribe::updateOrCreate(
            [
                'patient_id' => $patient->getKey()
            ],
            [
                'phone' => $patient->cell_phone,
                'status' => TwilioSubscribe::UNSUBSCRIBE,
            ]
        );
    }

    private function getPatient(string $phone): Patient
    {
        $phone = mb_substr($phone, 3);
        
        $phoneMask = sprintf("%s-%s-%s",
            substr($phone, 0, 3),
            substr($phone, 3, 3),
            substr($phone, 6)
        );

        return Patient::select(['id', 'cell_phone'])
            ->where('cell_phone', $phoneMask)
            ->firstOrFail();
    }
}
