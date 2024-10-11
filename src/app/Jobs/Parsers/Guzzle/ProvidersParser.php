<?php

namespace App\Jobs\Parsers\Guzzle;

use App\DTO\OfficeAlly\ProviderDTO;
use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Option;
use App\Provider;
use App\Models\PatientHasProvider;
use App\Helpers\ExceptionNotificator;
use App\Notifications\AnErrorOccurred;

class ProvidersParser extends AbstractParser
{
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handleParser()
    {
        $officeAllyHelper = app()->make(OfficeAllyHelper::class)(Option::OA_ACCOUNT_1);
        $providers = $officeAllyHelper->getProviderList();
        if ($providers === null) {
            return;
        }

        $providerIds = $this->providersCrawler($providers);
        $providerIds = array_unique($providerIds);

        if (count($providers) && !count($providerIds)) {
            with(new ExceptionNotificator())
                ->officeAllyNotifyAndSendToSentry(new AnErrorOccurred(sprintf('Parsed %d providers, but saved %d', count($providers), count($providerIds))));
        }
        
        if (count($providerIds)) {
            \Bus::dispatchNow(new ProviderProfilesParser($providerIds));
        }
    }
    
    private function providersCrawler($providers)
    {
        $providerIds = [];

        foreach ($providers as $item) {
            // $providerData = [ 
            //     'officeally_id' => $item['cell'][0],
            //     'provider_name' => $item['cell'][2] . ' ' . $item['cell'][1],
            //     'phone'         => $this->sanitizePhone($item['cell'][4]), 
            // ];

            $providerData = new ProviderDTO([
                'officeally_id' => $item['cell'][0],
                'provider_name' => $item['cell'][2] . ' ' . $item['cell'][1],
                'phone'         => $this->sanitizePhone($item['cell'][4]), 
            ]);

            $providerData = $providerData->toArray();
           
            if(!$providerData['officeally_id']) {
                continue;
            }
            
            $provider = Provider::query()
                ->where('officeally_id', $providerData['officeally_id'])
                ->withTrashed()
                ->first();
            if ($provider) {
                if(!empty($provider->phone)) {
                    unset($providerData['phone']);
                }
                $provider->update($providerData);
            } else {
                $provider = Provider::create($providerData);

                PatientHasProvider::create([
                    'patients_id' => 1111,
                    'providers_id' => $provider->id
                ]);
            }
            
            $providerIds[] = $providerData['officeally_id'];
        }

        return $providerIds;
    }
    
    /**
     * @param $phone
     *
     * @return string|null
     */
    private function sanitizePhone($phone)
    {
        $phone = trim(str_replace('-', '', $phone));
        
        return empty($phone) ? null : $phone;
    }
}
