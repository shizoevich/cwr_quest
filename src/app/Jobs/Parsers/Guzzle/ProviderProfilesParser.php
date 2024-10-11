<?php

namespace App\Jobs\Parsers\Guzzle;

use App\DTO\OfficeAlly\ProviderInfoDTO;
use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Option;
use App\Provider;
use Symfony\Component\DomCrawler\Crawler;
use App\Helpers\ExceptionNotificator;
use App\Notifications\AnErrorOccurred;

class ProviderProfilesParser extends AbstractParser
{
    /**
     * @var array
     */
    private $providerIds;

    /**
     * ProviderProfilesParser constructor.
     *
     * @param array $providerIds
     */
    public function __construct(array $providerIds)
    {
        $this->providerIds = $providerIds;
        parent::__construct();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handleParser()
    {
        $officeAllyHelper = app()->make(OfficeAllyHelper::class)(Option::OA_ACCOUNT_1);

        $savedProviders = 0;
        foreach ($this->providerIds as $providerId) {
            $profilePage = $officeAllyHelper->getProviderProfile($providerId);

            $this->providerCrawler($providerId, $profilePage);

            $savedProviders++;
        }

        if ($savedProviders < count($this->providerIds)) {
            with(new ExceptionNotificator())
                ->officeAllyNotifyAndSendToSentry(new AnErrorOccurred(sprintf('Parsed %d providers, but saved %d', count($this->providerIds), $savedProviders)));
        }
    }

    private function providerCrawler($providerId, $profilePage)
    {
        $crawler = new Crawler($profilePage);

        $speciality = $crawler->filter('input#ctl00_phFolderContent_ucProviderForm_NUCCSearch_txtSpecialty')->first()->attr('value');
        $matches = [];
        preg_match('/Taxonomy: (?<code>.+)$/', $speciality, $matches);

        // $infoDTO = [
        //     'first_name' => $crawler->filter('input#ctl00_phFolderContent_ucProviderForm_FirstName')->first()->attr('value'),
        //     'last_name' => $crawler->filter('input#ctl00_phFolderContent_ucProviderForm_LastName')->first()->attr('value'),
        //     'middle_initial' => $crawler->filter('input#ctl00_phFolderContent_ucProviderForm_MiddleName')->first()->attr('value'),
        //     'license_no' => $crawler->filter('input#ctl00_phFolderContent_ucProviderForm_StateLicenseID')->first()->attr('value'),
        //     'individual_npi' => $crawler->filter('input#ctl00_phFolderContent_ucProviderForm_NPI')->first()->attr('value'),
        //     'taxonomy_code' => $matches['code'] ?? null,
        // ];

        $infoDTO = new ProviderInfoDTO([
            'first_name' => $crawler->filter('input#ctl00_phFolderContent_ucProviderForm_FirstName')->first()->attr('value'),
            'last_name' => $crawler->filter('input#ctl00_phFolderContent_ucProviderForm_LastName')->first()->attr('value'),
            'middle_initial' => $crawler->filter('input#ctl00_phFolderContent_ucProviderForm_MiddleName')->first()->attr('value'),
            'license_no' => $crawler->filter('input#ctl00_phFolderContent_ucProviderForm_StateLicenseID')->first()->attr('value'),
            'individual_npi' => $crawler->filter('input#ctl00_phFolderContent_ucProviderForm_NPI')->first()->attr('value'),
            'taxonomy_code' => $matches['code'] ?? null,
        ]);
        $infoDTO = $infoDTO->toArray(); 

        Provider::withTrashed()
            ->where('officeally_id', $providerId)
            ->update($infoDTO);
    }
}
