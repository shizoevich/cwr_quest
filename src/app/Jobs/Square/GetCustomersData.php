<?php

namespace App\Jobs\Square;

use App\Repositories\Square\ApiRepositoryInterface as SquareApiRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;

/**
 * Class GetCustomersData
 * @package App\Jobs\Square
 */
class GetCustomersData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Collection
     */
    private $customers;
    /**
     * @var SquareApiRepositoryInterface
     */
    private $squareApi;
    
    /**
     * GetCustomersData constructor.
     *
     * @param Collection                   $customers
     */
    public function __construct(Collection $customers)
    {
        $this->squareApi = app()->make(SquareApiRepositoryInterface::class);
        $this->customers = $customers;
    }

   
    public function handle()
    {
        foreach ($this->customers as $customer) {
            $squareCustomer = $this->squareApi->getCustomer($customer->external_id);
            if(!$squareCustomer) {
                continue;
            }
            $customer->first_name = $squareCustomer->getGivenName();
            $customer->last_name = $squareCustomer->getFamilyName();
            $customer->email = $squareCustomer->getEmailAddress();
            $customer->save();
        }
    }
}
