<?php


namespace App\Components\PatientForm\ConfidentialInformation;


use App\Components\PatientForm\BasePatientForm;

class ConfidentialInformationForm extends BasePatientForm
{
    /** @var string  */
    protected $templateName = 'authorization_to_release_confidential_information.pdf';

    public function getFontSize()
    {
        return 10;
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
                            'x' => 81,
                            'y' => 74,
                            'font' => null, // if same as default leave null
                            'font_size' => null, // if same as default leave null
                        ],
                        [
                            'field' => 'date_of_birth',
                            'value' => '',
                            'max_length' => 10,
                            'x' => 168,
                            'y' => 74,
                            'font' => null,
                            'font_size' => null,
                        ],
                        [
                            'field' => 'name',
                            'value' => '',
                            'max_length' => 42,
                            'x' => 58.5,
                            'y' => 106,
                            'font' => null,
                            'font_size' => null,
                        ],
                        [
                            'field' => 'hereby_information_with',
                            'value' => '',
                            'max_length' => 86,
                            'x' => 54,
                            'y' => 123.7,
                            'font' => null,
                            'font_size' => null,
                        ],
                        [
                            'field' => 'name',
                            'value' => '',
                            'max_length' => 42,
                            'x' => 114.5,
                            'y' => 192.5,
                            'font' => null,
                            'font_size' => null,
                        ],
                        [
                            'field' => 'date',
                            'value' => date('m/d/Y'),
                            'max_length' => 10,
                            'x' => 171.5,
                            'y' => 192.5,
                            'font' => null,
                            'font_size' => null,
                        ],
                        [
                            'field' => 'guardian_name',
                            'value' => '',
                            'max_length' => 27,
                            'x' => 114.5,
                            'y' => 210.5,
                            'font' => null,
                            'font_size' => null,
                        ],
                        [
                            'field' => 'relationship',
                            'value' => '',
                            'max_length' => 18,
                            'x' => 171.5,
                            'y' => 210.5,
                            'font' => null,
                            'font_size' => null,
                        ]
                    ],
                    'checkboxes' => [],
                    'radiobuttons' => [],
                    'images' => [
                        [
                            'field' => 'signature',
                            'value' => '',
                            'max_width' => 63,
                            'x' => 45,
                            'y' => 178,
                            'width' => null,
                            'height' => 15,
                            'file_name' => ''
                        ],
                        [
                            'field' => 'signature18',
                            'value' => '',
                            'max_width' => 63,
                            'x' => 45,
                            'y' => 197.5,
                            'width' => null,
                            'height' => 14,
                            'file_name' => ''
                        ]
                    ]
                ]
            ]
        ];
    }
}