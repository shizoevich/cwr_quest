<?php

namespace App\Repositories\Appointment\Payment;

/**
 * Class CheckPaymentRepository
 * @package App\Repositories\Appointment\Payment
 */
class CheckPaymentRepository extends AbstractPaymentRepository
{
    
    /**
     * @inheritDoc
     */
    public function pay(): bool
    {
        //Now Square don't allow adding check payments via API
        return true;
    }
    
    /**
     * @inheritDoc
     */
    protected function getOfficeAllyPaymentMethod(): int
    {
        return 2;
    }
}