<?php

namespace App\Repositories\Appointment\Payment;

use App\Components\Square\Customer;
use App\Repositories\Square\ApiRepositoryInterface;

/**
 * Class InvoicePaymentRepository
 * @package App\Repositories\Appointment\Payment
 */
class InvoicePaymentRepository extends AbstractPaymentRepository
{
    
    /**
     * @inheritDoc
     */
    public function pay(): bool
    {
        $locationId = 'ZHMQM6QQRRANS';   //@todo change location
        $customerService = new Customer();
        $squareAccount = $customerService->createIfNotExist($this->patient);
        $squareApi = app()->make(ApiRepositoryInterface::class);
        if(!$squareAccount->email || $squareAccount->email !== $this->payload['email']) {
            $squareApi->updateCustomer($squareAccount->external_id, ['email' => $this->payload['email']]);
        }
        
        $order = $squareApi->createOrder($squareAccount->external_id, $locationId, [
            [
                'name' => 'Co-Pay',
//                'note' => '',
                'cost' => $this->payload['amount'],
                'quantity' => 1,
            ]
        ]);
        $invoice = $squareApi->createInvoice($squareAccount->external_id, $locationId, $order->getId());
        $squareApi->publishInvoice($invoice->getId(), $invoice->getVersion());
        
        return true;
    }
    
    /**
     * @inheritDoc
     */
    protected function getOfficeAllyPaymentMethod(): int
    {
        return 3;   //credit card payment
    }
}