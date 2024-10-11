<?php
/**
 * Created by PhpStorm.
 * User: braginec_dv
 * Date: 29.09.2017
 * Time: 8:58
 */

namespace App\Exceptions;

use Throwable;

class RingcentralException extends \Exception
{

    protected $code;

    public function __construct(
        $message = "",
        $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->code = $code;
    }
}