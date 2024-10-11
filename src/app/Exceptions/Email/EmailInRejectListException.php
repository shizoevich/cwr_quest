<?php

namespace App\Exceptions\Email;

use Throwable;

class EmailInRejectListException extends \Exception
{
    private $email;

    public function __construct($email = "", $message = "", $code = 0, Throwable $previous = null)
    {
        if (!$message) {
            $message = 'The email address is in the reject list.';
        }

        $this->email = $email;

        parent::__construct($message, $code, $previous);
    }

    public function getEmail()
    {
        return $this->email;
    }
}
