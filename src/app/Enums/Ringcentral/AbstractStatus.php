<?php

namespace App\Enums\Ringcentral;

abstract class AbstractStatus
{
    const MAPPED_STATUSES = [];
    
    public static function getNameByStatus($searchableStatus)
    {
        if(!$searchableStatus) {
            return null;
        }
        $statuses = static::getMappedStatuses();
        foreach ($statuses as $name => $status) {
            if($status == $searchableStatus) {
                return $name;
            }
        }
        
        return null;
    }
    
    public static function getStatusByName($searchableStatus)
    {
        if(!$searchableStatus) {
            return null;
        }
        $statuses = static::getMappedStatuses();
        
        return data_get($statuses, $searchableStatus);
    }
    
    protected static function getMappedStatuses()
    {
        return static::MAPPED_STATUSES;
    }
}