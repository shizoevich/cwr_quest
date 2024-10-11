<?php

namespace Tests\Helpers\OfficeAlly;

use Symfony\Component\DomCrawler\Crawler;

class ProviderProfileOfficeAllyHelper
{
    public static function getProviderProfileDataFromHtml($providerProfileHtml): array
    {
        $crawler = new Crawler($providerProfileHtml);

        return [
            'license_no' => $crawler->filter('input#ctl00_phFolderContent_ucProviderForm_StateLicenseID')->first()->attr('value'),
            'individual_npi' => $crawler->filter('input#ctl00_phFolderContent_ucProviderForm_NPI')->first()->attr('value'),
        ];
    }

    public static function getStructureProviderProfileData(): array
    {
        return [
            'license_no' => 'string',
            'individual_npi' => 'string',
        ];
    }

    public static function getProviderProfileDataForHtml(): array
    {
        return [
            'license_no' => 'LMFT12345',
            'individual_npi' => '1234567890'
        ];
    }

    public static function getMockProviderProfileHtml($providerProfileData): string
    {
        return '<div>
                    <input id="ctl00_phFolderContent_ucProviderForm_StateLicenseID" value="' . $providerProfileData['license_no'] . '" />
                    <input id="ctl00_phFolderContent_ucProviderForm_NPI" value="' . $providerProfileData['individual_npi'] . '"/>
                </div>';
    }

}