<?php

namespace App\Jobs\Square;

use App\Models\Square\SquareLocation;
use App\Repositories\Square\ApiRepositoryInterface as SquareApiRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Square\Models\Location;

/**
 * Class GetLocations
 * @package App\Jobs\Square
 */
class GetLocations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    /**
     * @var SquareApiRepositoryInterface
     */
    private $squareApi;
    
    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->squareApi = app()->make(SquareApiRepositoryInterface::class);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $locations = $this->squareApi->getLocations();
        $this->saveLocations($locations);
    }
    
    /**
     * @param Location[] $locations
     */
    protected function saveLocations($locations) {
        foreach($locations as $location) {
            $address = $location->getAddress();
            SquareLocation::updateOrCreate([
                'external_id' => $location->getId(),
            ], [
                'external_id' => $location->getId(),
                'name' => $location->getName(),
                'address_line_1' => $address->getAddressLine1(),
                'address_line_2' => $address->getAddressLine2(),
                'locality' => $address->getLocality(),
                'administrative_district_level_1' => $address->getAdministrativeDistrictLevel1(),
                'postal_code' => $address->getPostalCode(),
                'country' => $address->getCountry(),
                'merchant_id' => $location->getMerchantId(),
                'currency' => $location->getCurrency(),
                'phone_number' => $location->getPhoneNumber(),
            ]);
        }
    }
}
