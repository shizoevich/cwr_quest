<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * App\PatientStatus
 *
 * @property int $id
 * @property string $status
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string $hex_color
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientStatus whereHexColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientStatus whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientStatus whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PatientStatus extends Model
{
    protected $table = 'patient_statuses';

    protected $guarded = [];

    public static function getId($status)
    {
        return static::select('id')->where('status', $status)->firstOrFail()['id'];
    }

    public static function getActiveId()
    {
        return static::getId('Active');
    } 

    public static function getArchivedId()
    {
        return static::getId('Archived');
    }

    public static function getInactiveId()
    {
        return static::getId('Inactive');
    }

    public static function getOtherId()
    {
        return static::getId('Other');
    }

    public static function getDischargedId()
    {
        return static::getId('Discharged');
    }

    public static function getLostId()
    {
        return static::getId('Lost');
    }

    public static function getNewId()
    {
        return static::getId('New');
    }

    public static function getStatusIds($statuses)
    {
        return static::select('id')
            ->whereIn('status', $statuses)
            ->pluck('id')
            ->toArray();
    }

    public static function changeStatusAutomatically($patientId, $comment)
    {
        $statusId = null;
        switch ($comment) {
            case "new_to_active":
            case "inactive_to_active":
            case "lost_to_active":
            case "archived_to_active":
            case "discharged_to_active":
                $statusId = static::getActiveId();
                break;
            case "active_to_inactive":
                $statusId = static::getInactiveId();
                break;
            case "to_discharged":
                $statusId = static::getDischargedId();
                break;
            case "new_to_lost":
            case 'inactive_to_lost':
                $statusId = static::getLostId();
                break;
            case 'to_new':
            case 'lost_to_new':
                $statusId = static::getNewId();
                break;
            case 'discharged_to_archived':
                $statusId = static::getArchivedId();
                break;
        }

        if (is_null($statusId)) {
            return;
        }

        if (is_array($patientId)) {
            if (count($patientId)) {
                Patient::whereIn('id', $patientId)->each(function (Patient $patient) use ($statusId) {
                    $patient->update(['status_id' => $statusId]);
                });
            }
        } else {
            Patient::find($patientId)->update(['status_id' => $statusId]);
        }

        PatientComment::addSystemComment($patientId, $comment, false, true);
    }

    public static function getChangeStatusPeriod(Carbon $lastVisitCreatedTime, string $configName, int $visitFrequencyId = null): int
    {
        $periodTransitionDate = Carbon::parse(config('patient_statuses.inactive_lost_periods_transition_date'));
        $config = self::getConfig($configName, $visitFrequencyId);

        return $lastVisitCreatedTime->gte($periodTransitionDate)
            ? $config['curr']
            : $config['prev'];
    }

    public static function getConfig(string $configName, int $visitFrequencyId = null)
    {
        if ($configName === 'inactive_to_lost') {
            return self::getInactiveToLostConfig($visitFrequencyId);
        }
        if ($configName === 'active_to_inactive' || $configName === 'inactive_to_active') {
            return self::getActiveToInactiveConfig($visitFrequencyId);
        }
        
        return [
            'curr' => 0,
            'prev' => 0
        ];
    }

    private static function getInactiveToLostConfig(int $visitFrequencyId = null)
    {
        $configList = [
            PatientVisitFrequency::getTwiceAWeekId() => [
                'curr' => config('patient_statuses.inactive_to_lost_curr'),
                'prev' => config('patient_statuses.inactive_to_lost_prev'),
            ],
            PatientVisitFrequency::getWeeklyId() => [
                'curr' => config('patient_statuses.inactive_to_lost_curr'),
                'prev' => config('patient_statuses.inactive_to_lost_prev'),
            ],
            PatientVisitFrequency::getBiweeklyId() => [
                'curr' => config('patient_statuses.biweekly_inactive_to_lost_curr'),
                'prev' => config('patient_statuses.inactive_to_lost_prev'),
            ],
            PatientVisitFrequency::getMonthlyId() => [
                'curr' => config('patient_statuses.monthly_inactive_to_lost_curr'),
                'prev' => config('patient_statuses.inactive_to_lost_prev'),
            ],
        ];

        if (isset($visitFrequencyId) && isset($configList[$visitFrequencyId])) {
            return $configList[$visitFrequencyId];
        }

        return [
            'curr' => config('patient_statuses.inactive_to_lost_curr'),
            'prev' => config('patient_statuses.inactive_to_lost_prev')
        ];
    }

    private static function getActiveToInactiveConfig(int $visitFrequencyId = null)
    {
        $configList = [
            PatientVisitFrequency::getTwiceAWeekId() => [
                'curr' => config('patient_statuses.active_to_inactive_curr'),
                'prev' => config('patient_statuses.active_to_inactive_prev'),
            ],
            PatientVisitFrequency::getWeeklyId() => [
                'curr' => config('patient_statuses.active_to_inactive_curr'),
                'prev' => config('patient_statuses.active_to_inactive_prev'),
            ],
            PatientVisitFrequency::getBiweeklyId() => [
                'curr' => config('patient_statuses.biweekly_active_to_inactive_curr'),
                'prev' => config('patient_statuses.active_to_inactive_prev'),
            ],
            PatientVisitFrequency::getMonthlyId() => [
                'curr' => config('patient_statuses.monthly_active_to_inactive_curr'),
                'prev' => config('patient_statuses.active_to_inactive_prev'),
            ],
        ];

        if (isset($visitFrequencyId) && isset($configList[$visitFrequencyId])) {
            return $configList[$visitFrequencyId];
        }

        return [
            'curr' => config('patient_statuses.active_to_inactive_curr'),
            'prev' => config('patient_statuses.active_to_inactive_prev'),
        ];
    }
}
