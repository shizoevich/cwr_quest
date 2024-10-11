<?php

namespace App\Exceptions\Square;

use Square\Models\Error;
use Throwable;

class SquareException extends \Exception
{
    /**
     * @var Error[]
     */
    protected $errors;
    
    public function __construct(array $errors, string $message = '', $code = 0, Throwable $previous = null)
    {
        $this->errors = $this->prepareErrors($errors);
        parent::__construct($message, $code, $previous);
    }
    
    /**
     * @param Error[] $errors
     */
    private function prepareErrors(array $errors)
    {
        foreach ($errors as &$error) {
            $detail = $error->getDetail();
            $error->original_detail = $detail;
            $key = 'validation.square.' . strtolower($error->getCategory()) . '.' . strtolower($error->getCode());
            $newDetail = trans($key);
            if($newDetail !== $key) {
                $error->setDetail($newDetail);
            }
        }
        
        return $errors;
    }
    
    /**
     * @return Error[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}