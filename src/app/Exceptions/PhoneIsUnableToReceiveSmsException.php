<?php

namespace App\Exceptions;

use Throwable;

class PhoneIsUnableToReceiveSmsException extends \Exception
{
    public function __construct($phone, $code = 0, Throwable $previous = null)
    {
        $message = sprintf('The %s is unable to receive SMS. Potential reasons could include trying to reach a landline or, in the case of short codes, an unreachable carrier.', $phone);
        
        parent::__construct($message, $code, $previous);
    }
}