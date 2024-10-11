<?php

namespace App\Components\PatientForm\PaymentForService;

use App\Components\PatientForm\BasePatientForm;

class PaymentForServiceForm extends BasePatientForm
{
    /** @var string */
    protected $templateName = 'payment_for_service_and_fee_arrangements.pdf';

    public function getFontSize()
    {
        return 10.5;
    }

    /**
     * @inheritDoc
     */
    public function getPdfFields()
    {
        return [
            'pages' => [
                [
                    'texts' => [
                        [
                            'field'      => 'co_pay',
                            'value'      => '',
                            'max_length' => 11,
                            'x'          => 156,
                            'y'          => 55,
                            'font'       => null,
                            'font_size'  => null,
                        ],
                        [
                            'field'      => 'payment_for_session_not_converted',
                            'value'      => '',
                            'max_length' => 10,
                            'x'          => 156,
                            'y'          => 62.5,
                            'font'       => null,
                            'font_size'  => null,
                        ],
                        [
                            'field'      => 'self_pay',
                            'value'      => '',
                            'max_length' => 10,
                            'x'          => 156,
                            'y'          => 71,
                            'font'       => null,
                            'font_size'  => null,
                        ],
                        [
                            'field'      => 'charge_for_cancellation',
                            'value'      => '',
                            'max_length' => 10,
                            'x'          => 156,
                            'y'          => 79,
                            'font'       => null,
                            'font_size'  => null,
                        ],
                        [
                            'field'      => 'other_charges',
                            'value'      => '',
                            'max_length' => 37,
                            'x'          => 52,
                            'y'          => 86.5,
                            'font'       => null,
                            'font_size'  => null,
                        ],
                        [
                            'field'      => 'other_charges_price',
                            'value'      => '',
                            'max_length' => 10,
                            'x'          => 156,
                            'y'          => 86.5,
                            'font'       => null,
                            'font_size'  => null,
                        ],
                    ],
                    'checkboxes' => [],
                    'radiobuttons' => [],
                    'images' => [],
                ],
                [
                    'texts' => [
                        [
                            'field'      => 'name',
                            'value'      => '',
                            'max_length' => 37,
                            'x'          => 97,
                            'y'          => 91,
                            'font'       => null,
                            'font_size'  => 8.5,
                        ],
                        [
                            'field'      => 'date',
                            'value'      => $this->date,
                            'max_length' => 0,
                            'x'          => 156,
                            'y'          => 91,
                            'font'       => null,
                            'font_size'  => 8.5,
                        ],
                        [
                            'field'      => 'guardian_name',
                            'value'      => '',
                            'max_length' => 38,
                            'x'          => 97,
                            'y'          => 104,
                            'font'       => null,
                            'font_size'  => 8.5,
                        ],
                        [
                            'field'      => 'relationship',
                            'value'      => '',
                            'max_length' => 27,
                            'x'          => 156,
                            'y'          => 104,
                            'font'       => null,
                            'font_size'  => 8.5,
                        ],
                    ],
                    'checkboxes' => [],
                    'radiobuttons' => [],
                    'images' => [
                        [
                            'field' => 'signature',
                            'value' => '',
                            'max_width' => 69,
                            'x' => 13,
                            'y' => 80.6,
                            'width' => null,
                            'height' => 12,
                            'file_name' => ''
                        ],
                        [
                            'field' => 'signature18',
                            'value' => '',
                            'max_width' => 69,
                            'x' => 13,
                            'y' => 95.6,
                            'width' => null,
                            'height' => 9,
                            'file_name' => ''
                        ],
                    ],
                ],
            ]
        ];
    }
}
