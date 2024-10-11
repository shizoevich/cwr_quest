<?php

namespace App\Exceptions\Officeally;

abstract class OfficeallyException extends \Exception
{
    private $originalMessage;
    
    /**
     * @return mixed|string
     */
    public function getOriginalMessage()
    {
        return isset($this->originalMessage) ? $this->originalMessage : $this->getMessage();
    }
    
    /**
     * @param mixed $originalMessage
     *
     * @return OfficeallyException
     */
    public function setOriginalMessage($originalMessage)
    {
        $this->originalMessage = $originalMessage;
        
        return $this;
    }
    
    /**
     * @return string
     */
    abstract public function getHumanReadableMessage(): string;
    
    /**
     * @return string
     */
    abstract public function getStatusCode(): int;
}