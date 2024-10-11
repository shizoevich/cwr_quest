<?php

namespace App\Components\PatientForm\NewPatient;

use App\Components\PatientForm\BasePatientForm;

class NewPatientForm extends BasePatientForm
{
    /** @var string */
    protected $templateName = 'new_patient_patient_information_informed_consent_privacy_notice.pdf';
    
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
                    'texts'        => [
                        [
                            'field'      => 'name',
                            'value'      => '',
                            'max_length' => 37,
                            'x'          => 34,
                            'y'          => 66.5,
                            'font'       => null,
                            'font_size'  => null,
                        ],
                        [
                            'field'      => 'date_of_birth',
                            'value'      => '',
                            'max_length' => 10,
                            'x'          => 162,
                            'y'          => 66.5,
                            'font'       => null,
                            'font_size'  => null,
                        ],
                        [
                            'field'      => 'home_address',
                            'value'      => '',
                            'max_length' => 87,
                            'x'          => 47.5,
                            'y'          => 75.5,
                            'font'       => null,
                            'font_size'  => null,
                        ],
                        [
                            'field'      => 'city',
                            'value'      => '',
                            'max_length' => 20,
                            'x'          => 31,
                            'y'          => 84.2,
                            'font'       => null,
                            'font_size'  => null,
                        ],
                        [
                            'field'      => 'state',
                            'value'      => '',
                            'max_length' => 5,
                            'x'          => 79,
                            'y'          => 84.2,
                            'font'       => null,
                            'font_size'  => null,
                        ],
                        [
                            'field'      => 'zip',
                            'value'      => '',
                            'max_length' => 11,
                            'x'          => 97,
                            'y'          => 84.2,
                            'font'       => null,
                            'font_size'  => null,
                        ],
                        [
                            'field'      => 'email',
                            'value'      => '',
                            'max_length' => 36,
                            'x'          => 132.5,
                            'y'          => 84.2,
                            'font'       => null,
                            'font_size'  => null,
                        ],
                        [
                            'field'      => 'home_phone',
                            'value'      => '',
                            'max_length' => 21,
                            'x'          => 48,
                            'y'          => 102.4,
                            'font'       => null,
                            'font_size'  => null,
                        ],
                        [
                            'field'      => 'mobile_phone',
                            'value'      => '',
                            'max_length' => 21,
                            'x'          => 48.2,
                            'y'          => 110.1,
                            'font'       => null,
                            'font_size'  => null,
                        ],
                        [
                            'field'      => 'work_phone',
                            'value'      => '',
                            'max_length' => 21,
                            'x'          => 48.2,
                            'y'          => 124.6,
                            'font'       => null,
                            'font_size'  => null,
                        ],
                        [
                            'field'      => 'emergency_contact',
                            'value'      => '',
                            'max_length' => 32,
                            'x'          => 55,
                            'y'          => 131.7,
                            'font'       => null,
                            'font_size'  => null,
                        ],
                        [
                            'field'      => 'emergency_contact_phone',
                            'value'      => '',
                            'max_length' => 14,
                            'x'          => 128.2,
                            'y'          => 131.7,
                            'font'       => null,
                            'font_size'  => null,
                        ],
                        [
                            'field'      => 'emergency_contact_relationship',
                            'value'      => '',
                            'max_length' => 13,
                            'x'          => 176,
                            'y'          => 131.7,
                            'font'       => null,
                            'font_size'  => null,
                        ],
                        [
                            'field'      => 'hear_about_us_other_specify',
                            'value'      => '',
                            'max_length' => 21,
                            'x'          => 157.8,
                            'y'          => 145.6,
                            'font'       => null,
                            'font_size'  => null,
                        ],
                        [
                            'field'      => 'referred_by_other_insurance_specify',
                            'value'      => '',
                            'max_length' => 24,
                            'x'          => 152.3,
                            'y'          => 158.5,
                            'font'       => null,
                            'font_size'  => null,
                        ],
                        //AGREEMENT FOR SERVICE / INFORMED CONSENT
                        [
                            'field'      => 'name',
                            'value'      => '',
                            'max_length' => 37,
                            'x'          => 58,
                            'y'          => 188.4,
                            'font'       => null,
                            'font_size'  => 7.5,
                        ],
                    ],
                    'checkboxes'   => [
                        [
                            'field' => 'yelp',
                            'value' => '',
                            'x'     => 23,
                            'y'     => 143.2,
                        ],
                        [
                            'field' => 'google',
                            'value' => '',
                            'x'     => 41.5,
                            'y'     => 143.2,
                        ],
                        [
                            'field' => 'yellow_pages',
                            'value' => '',
                            'x'     => 63,
                            'y'     => 143.2,
                        ],
                        [
                            'field' => 'event_i_attended',
                            'value' => '',
                            'x'     => 92,
                            'y'     => 143.2,
                        ],
                        [
                            'field' => 'hear_about_us_other',
                            'value' => '',
                            'x'     => 127,
                            'y'     => 143.2,
                        ],
                        //I was referred by:
                        [
                            'field' => 'friend_or_relative',
                            'value' => '',
                            'x'     => 23,
                            'y'     => 155.9,
                        ],
                        [
                            'field' => 'another_professional',
                            'value' => '',
                            'x'     => 58,
                            'y'     => 155.9,
                        ],
                        [
                            'field' => 'kaiser',
                            'value' => '',
                            'x'     => 101.7,
                            'y'     => 155.9,
                        ],
                        [
                            'field' => 'referred_by_other_insurance',
                            'value' => '',
                            'x'     => 121.5,
                            'y'     => 155.9,
                        ],
                    ],
                    'radiobuttons' => [
                        [
                            'field' => 'allow_mailing',
                            'yes'   => [
                                'value' => '',
                                'x'     => 170.2,
                                'y'     => 89,
                            ],
                            'no'    => [
                                'value' => '',
                                'x'     => 185.7,
                                'y'     => 89,
                            ],
                        ],
                        [
                            'field' => 'allow_home_phone_call',
                            'yes'   => [
                                'value' => '',
                                'x'     => 170.2,
                                'y'     => 98.4,
                            ],
                            'no'    => [
                                'value' => '',
                                'x'     => 185.7,
                                'y'     => 98.4,
                            ],
                        ],
                        [
                            'field' => 'allow_mobile_phone_call',
                            'yes'   => [
                                'value' => '',
                                'x'     => 170.2,
                                'y'     => 105.8,
                            ],
                            'no'    => [
                                'value' => '',
                                'x'     => 185.7,
                                'y'     => 105.8,
                            ],
                        ],
                        [
                            'field' => 'allow_mobile_send_messages',
                            'yes'   => [
                                'value' => '',
                                'x'     => 170.2,
                                'y'     => 113,
                            ],
                            'no'    => [
                                'value' => '',
                                'x'     => 185.7,
                                'y'     => 113,
                            ],
                        ],
                        [
                            'field' => 'allow_work_phone_call',
                            'yes'   => [
                                'value' => '',
                                'x'     => 170.2,
                                'y'     => 120.5,
                            ],
                            'no'    => [
                                'value' => '',
                                'x'     => 185.7,
                                'y'     => 120.5,
                            ],
                        ],
                    ],
                    'images'       => []
                ],
                [
                    'texts' => [],
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
                            'x'          => 17,
                            'y'          => 196,
                            'font'       => null,
                            'font_size'  => 8.5,
                        ],
                        [
                            'field'      => 'name',
                            'value'      => '',
                            'max_length' => 37,
                            'x'          => 97,
                            'y'          => 230,
                            'font'       => null,
                            'font_size'  => 8.5,
                        ],
                        [
                            'field'      => 'date',
                            'value'      => $this->date,
                            'max_length' => 37,
                            'x'          => 156,
                            'y'          => 230,
                            'font'       => null,
                            'font_size'  => 8.5,
                        ],
                        [
                            'field'      => 'guardian_name',
                            'value'      => '',
                            'max_length' => 38,
                            'x'          => 97,
                            'y'          => 243,
                            'font'       => null,
                            'font_size'  => 8.5,
                        ],
                        [
                            'field'      => 'relationship',
                            'value'      => '',
                            'max_length' => 27,
                            'x'          => 156,
                            'y'          => 243,
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
                            'y' => 219,
                            'width' => null,
                            'height' => 9,
                            'file_name' => ''
                        ],
                        [
                            'field' => 'signature18',
                            'value' => '',
                            'max_width' => 69,
                            'x' => 13,
                            'y' => 234,
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