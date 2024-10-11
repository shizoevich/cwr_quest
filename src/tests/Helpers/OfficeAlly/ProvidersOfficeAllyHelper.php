<?php

namespace Tests\Helpers\OfficeAlly;

class ProvidersOfficeAllyHelper
{
    public static function getProviderDataFromJson($providers)
    {
        $providerData = null;

        foreach ($providers as $item) {
            $providerData = [
                'officeally_id' => $item['cell'][0],
                'provider_name' => $item['cell'][2] . ' ' . $item['cell'][1],
                'phone'         => static::sanitizePhone($item['cell'][4]),
            ];

            if(!$providerData['officeally_id']) {
                continue;
            }

            break;
        }
        
        return $providerData;
    }

    public static function getStructureProviderData(): array
    {
        return [
            'officeally_id' => 'int',
            'provider_name' => 'string',
            'phone' => 'string'
        ];
    }

    public static function getProviderDataForHtml(): array
    {
        return [
            'officeally_id' => 123456,
            'last_name' => 'Test, test',
            'first_name' => 'John',
            'speciality' => 'Clinical',
            'phone' => '111-111-1111',
            'login' => 'login'
        ];
    }

    public static function getMockProvidersData($providerData): array
    {
        return [
            [
                "id" => $providerData['officeally_id'],
                "cell" => [
                    $providerData['officeally_id'],
                    $providerData['last_name'],
                    $providerData['first_name'],
                    $providerData['speciality'],
                    $providerData['phone'],
                    $providerData['login'],
                    1,
                    0,
                    false,
                    null,
                    null,
                    null
                ]
            ],
        ];
    }

    public static function sanitizePhone($phone): ?string
    {
        $phone = trim(str_replace('-', '', $phone));
        if(empty($phone)) {
            return null;
        }

        return !empty($phone) ? $phone : null;
    }
}