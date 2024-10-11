<?php

namespace App\Repositories\Appointment\Payment;

/**
 * Class CashPaymentRepository
 * @package App\Repositories\Appointment\Payment
 */
class CashPaymentRepository extends AbstractPaymentRepository
{
    
    /**
     * @inheritDoc
     */
    public function pay(): bool
    {
        //Now Square don't allow adding cash payments via API
        return true;
    }
    
    /**
     * @inheritDoc
     */
    protected function getOfficeAllyPaymentMethod(): int
    {
        return 1;
    }
}