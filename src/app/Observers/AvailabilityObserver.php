<?php

namespace App\Observers;

use App\Availability;
use App\Events\AvailabilityChanged;
use App\Jobs\Availability\UpdateRemainingLengthForAvailability;
use App\Helpers\HIPAALogger;

class AvailabilityObserver
{
    /**
     * @param Availability $availability
     */
    public function created(Availability $availability)
    {
        event(new AvailabilityChanged($availability->provider_id, $availability->start_date));

        dispatch(new UpdateRemainingLengthForAvailability($availability));

        HIPAALogger::logEvent([
            'collection' => class_basename($availability),
            'event' => 'create',
            'data' => $availability->getLogData(),
            'message' => $availability->getCreateLogMessage(),
        ]);
    }

    /**
     * @param Availability $availability
     */
    public function updated(Availability $availability)
    {
        event(new AvailabilityChanged($availability->provider_id, $availability->start_date));
        
        dispatch(new UpdateRemainingLengthForAvailability($availability));

        $dirtyFields = $availability->getDirtyWithOriginal();
        HIPAALogger::logEvent([
            'collection' => class_basename($availability),
            'event' => 'update',
            'data' => $availability->getLogData(),
            'dirty_fields' => $dirtyFields,
            'message' => $availability->getUpdateLogMessage($dirtyFields),
        ]);
    }

    /**
     * @param Availability $availability
     */
    public function deleted(Availability $availability)
    {
        event(new AvailabilityChanged($availability->provider_id, $availability->start_date));

        HIPAALogger::logEvent([
            'collection' => class_basename($availability),
            'event' => 'delete',
            'data' => $availability->getLogData(),
            'message' => $availability->getDeleteLogMessage(),
        ]);
    }
}