<?php

namespace App\Exceptions\Officeally\Appointment;

use App\Exceptions\Officeally\OfficeallyException;

class AppointmentNotUpdatedException extends OfficeallyException
{
    /**
     * @var array
     */
    private $newPayload;
    /**
     * @var array
     */
    private $actualPayload;
    
    /**
     * AppointmentNotUpdated constructor.
     *
     * @param string $message
     * @param array  $newPayload
     * @param array  $actualPayload
     */
    public function __construct(array $newPayload, array $actualPayload, string $message = '')
    {
        $this->newPayload = $newPayload;
        $this->actualPayload = $actualPayload;
        $this->setOriginalMessage($message);
        $message = sprintf('%s%sNew Payload:%s%s%sActual Payload:%s%s', $message, PHP_EOL, PHP_EOL, json_encode($newPayload), PHP_EOL, PHP_EOL, json_encode($actualPayload));
        
        parent::__construct($message, 0, null);
    }
    
    /**
     * @inheritDoc
     */
    public function getHumanReadableMessage(): string
    {
        return 'Appointment is not updated (OfficeAlly unavailable). Please try again later.';
    }
    
    public function getStatusCode(): int
    {
        return 409;
    }
}