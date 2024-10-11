<?php

namespace App\Exceptions\Email;

use Throwable;

class EmailNotSentException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        if(!$message) {
            $message = 'Email cannot be sent. Please enter a valid / another email address.';
        }
        parent::__construct($message, $code, $previous);
    }
}