<?php

namespace App\Jobs\Square;

use App\PatientSquareAccount;
use App\Repositories\Square\ApiRepositoryInterface as SquareApiRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;
use Square\Models\Card;
use Square\Models\Customer;

/**
 * Class GetCustomersData
 * @package App\Jobs\Square
 */
class SyncCustomerData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var SquareApiRepositoryInterface
     */
    private $squareApi;
    /**
     * @var PatientSquareAccount
     */
    private $squareAccount;
    
    /**
     * SyncCustomerData constructor.
     *
     * @param PatientSquareAccount $squareAccount
     */
    public function __construct(PatientSquareAccount $squareAccount)
    {
        $this->squareApi = app()->make(SquareApiRepositoryInterface::class);
        $this->squareAccount = $squareAccount;
    }

   
    public function handle()
    {
        $squareCustomer = $this->squareApi->getCustomer($this->squareAccount->external_id);
        if(!$squareCustomer) {
            return;
        }
        
        $this->syncCustomerData($squareCustomer);
        $this->syncCustomerCardsData($squareCustomer);
    }
    
    /**
     * @param Customer $squareCustomer
     */
    private function syncCustomerData(Customer $squareCustomer)
    {
        $this->squareAccount->update([
            'first_name' => $squareCustomer->getGivenName(),
            'last_name' => $squareCustomer->getFamilyName(),
            'email' => $squareCustomer->getEmailAddress(),
        ]);
    }
    
    /**
     * @param Customer $squareCustomer
     */
    private function syncCustomerCardsData(Customer $squareCustomer)
    {
        $cards = $squareCustomer->getCards();
        if(empty($cards)) {
            $this->squareAccount
                ->cards()
                ->each(function ($card) {
                    $card->delete();
                });
            return;
        }
        $synchronizedCards = [];
        foreach ($cards as $card) {
            /** @var Card $card */
            $synchronizedCards[] = $card->getId();
            $this->squareAccount->cards()->updateOrCreate([
                'patient_square_account_id' => $this->squareAccount->id,
                'card_id' => $card->getId(),
            ], [
                'card_brand' => $card->getCardBrand(),
                'last_four' => $card->getLast4(),
                'exp_month' => $card->getExpMonth(),
                'exp_year' => $card->getExpYear(),
                'cardholder_name' => $card->getCardholderName(),
                'address_line_one' => optional($card->getBillingAddress())->getAddressLine1(),
                'address_line_two' => optional($card->getBillingAddress())->getAddressLine2(),
                'locality' => optional($card->getBillingAddress())->getLocality(),
                'administrative_district_level_one' => optional($card->getBillingAddress())->getAdministrativeDistrictLevel1(),
                'postal_code' => optional($card->getBillingAddress())->getPostalCode(),
                'country' => optional($card->getBillingAddress())->getCountry(),
            ]);
        }
        $this->squareAccount
            ->cards()
            ->whereNotIn('card_id', $synchronizedCards)
            ->each(function ($card) {
                $card->delete();
            });;
    }
}
