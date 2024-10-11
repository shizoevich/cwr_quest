<?php

namespace Tests\Helpers\OfficeAlly;

use Carbon\Carbon;

class PaymentsOfficeAllyHelper 
{
    public const PATIENT_ID = 67304048;
    public const PAYMENT_AMOUNT = 0;
    public const APPLIED_AMMOUNT = 0;

    public static function getPaymentDataFromJson($payments)
    {
        $paymentData = null;

        foreach ($payments as $payment) {
            $paymentData = [
                'external_id' => $payment['id'],
                'patient_id' => $payment['cell'][15],
                'transaction_type' => $payment['cell'][4],
                'payment_amount' => $payment['cell'][8] * 100,
                'applied_amount' => $payment['cell'][9] * 100,
                'transaction_date' => Carbon::createFromFormat('m/d/Y', $payment['cell'][1]),
            ];

            if(!$paymentData['patient_id']) {
                continue;
            }

            break;
        }
        
        return $paymentData;
    }

    public static function getStructurePaymentData(): array
    {
        return [
            'external_id' => 'int',
            'patient_id' => 'int',
            'transaction_type' => 'string',
            'payment_amount' => 'numeric',
            'applied_amount' => 'numeric',
        ];
    }

    public static function getPaymentData(): array
    {
        return [
            'external_id' => 123456,
            'patient_id' => 1,
            'transaction_type' => 'Test Type',
            'payment_amount' => 150,
            'applied_amount' => 250,
            'transaction_date' => '01/31/2023',
        ];
    }

    public static function getMockPaymentsData($paymentData): array
    {
        return [
            [
                'id' => $paymentData['external_id'],
                'cell' => [
                    $paymentData['external_id'],
                    $paymentData['transaction_date'],
                    null,
                    null,
                    $paymentData['transaction_type'],
                    null,
                    null,
                    null,
                    $paymentData['payment_amount'],
                    $paymentData['applied_amount'],
                    null,
                    null,
                    null,
                    null,
                    null,
                    $paymentData['patient_id'],
                ]
            ],
        ];
    }
}