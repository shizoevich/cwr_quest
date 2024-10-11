<?php


namespace App\Components\PatientForm\Telehealth;


use App\Components\PatientForm\BasePatientForm;

class TelehealthForm extends BasePatientForm
{
    /** @var string  */
    protected $templateName = 'telehealth.pdf';

    public function getFontSize()
    {
        return 11;
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
                            'field' => 'name',
                            'value' => '',
                            'max_length' => 42,
                            'x' => 28,
                            'y' => 210,
                            'font' => null, // if same as default leave null
                            'font_size' => null, // if same as default leave null
                        ],
                    ],
                    'checkboxes' => [],
                    'radiobuttons' => [],
                    'images' => []
                ],
                [
                    'texts' => [
                        [
                            'field' => 'home_address',
                            'value' => '',
                            'max_length' => 60,
                            'x' => 26,
                            'y' => 104,
                            'font' => null, // if same as default leave null
                            'font_size' => null, // if same as default leave null
                        ],
                        [
                            'field' => 'emergency_contact',
                            'value' => '',
                            'max_length' => 50,
                            'x' => 26,
                            'y' => 120,
                            'font' => null, // if same as default leave null
                            'font_size' => null, // if same as default leave null
                        ],
                    ],
                    'checkboxes' => [],
                    'radiobuttons' => [],
                    'images' => []
                ],
                [
                    'texts' => [
                        [
                            'field' => 'name',
                            'value' => '',
                            'max_length' => 42,
                            'x' => 26,
                            'y' => 58,
                            'font' => null, // if same as default leave null
                            'font_size' => null, // if same as default leave null
                        ],
                        [
                            'field' => 'date',
                            'value' => $this->date,
                            'max_length' => 42,
                            'x' => 26,
                            'y' => 105.5,
                            'font' => null, // if same as default leave null
                            'font_size' => null, // if same as default leave null
                        ],
                    ],
                    'checkboxes' => [],
                    'radiobuttons' => [],
                    'images' => [
                        [
                            'field' => 'signature',
                            'value' => '',
                            'max_width' => 69,
                            'x' => 26,
                            'y' => 71,
                            'width' => null,
                            'height' => 10,
                            'file_name' => ''
                        ],
                    ]
                ]
            ]
        ];
    }
}