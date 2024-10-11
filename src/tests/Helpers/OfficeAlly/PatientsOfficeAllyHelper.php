<?php

namespace Tests\Helpers\OfficeAlly;

use Carbon\Carbon;

class PatientsOfficeAllyHelper
{
    public const OA_PATIENT_PATIENT_ID = 67304048;
    public const OA_PATIENT_FIRST_NAME = 'Abc-3';
    public const OA_PATIENT_LAST_NAME = 'Xyz-3';
    public const OA_PATIENT_DATE_OF_BIRTH = '1962-04-01';

    public static function getPatientListDataRaw(): array
    {
        return [
            [
                'id' => self::OA_PATIENT_PATIENT_ID,
                'cell' => [
                    self::OA_PATIENT_PATIENT_ID, //  'patient_id'
                    self::OA_PATIENT_LAST_NAME, //  'last_name'
                    self::OA_PATIENT_FIRST_NAME, //  'first_name'
                    null,
                    null,
                    null,
                    '', //  'middle_initial'
                    self::OA_PATIENT_DATE_OF_BIRTH, //  'date_of_birth'
                    'M', //  'sex'
                    '12345', //  'patient_account_number'
                    'Aetna', //  'primary_insurance',
                    'P', // Type
                    'Active', // 'status'
                    1,
                    1,
                    null
                ],
            ],
        ];
    }

    public static function getPatientListDataFromRaw(array $patients): array
    {
        $patientsData = [];

        foreach ($patients as $item) {
            $dateOfBirth = $item['cell'][7];
            if (!empty($dateOfBirth)) {
                $dateOfBirth = Carbon::parse($dateOfBirth);
            } else {
                $dateOfBirth = null;
            }
            $patientsData[] = [
                'patient_id' => $item['cell'][0],
                'last_name' => $item['cell'][1],
                'first_name' => $item['cell'][2],
                'middle_initial' => $item['cell'][6],
                'date_of_birth' => optional($dateOfBirth)->toDateString(),
            ];
        }

        return $patientsData;
    }

    public static function getStructurePatientListItemData(): array
    {
        return [
            'patient_id' => 'int',
            'first_name' => 'string',
            'last_name' => 'string',
            'middle_initial' => 'string',
            'date_of_birth' => 'string',
        ];
    }
}