<?php

namespace App\Exceptions;

use Throwable;

class EmptyGoogleAccountException extends \Exception
{
    /**
     * EmptyGoogleAccountException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = trans('exception.google_service.empty_credentials');

        parent::__construct($message, 500, $previous);
    }
}