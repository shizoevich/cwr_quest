<?php

namespace App\Models\Patient;

use App\LoggableModelInterface;
use App\Patient;
use App\Provider;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\Patient\PatientRemoveRequest
 *
 * @property int $id
 * @property int $provider_id
 * @property int $patient_id
 * @property string $reason
 * @property int $approver_id
 * @property string $approver_comment
 * @property int $status
 * @property \Carbon\Carbon|null $approved_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\User|null $approver
 * @property-read \App\Patient $patient
 * @property-read \App\Provider $provider
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientRemovalRequest accepted()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientRemovalRequest canceled()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientRemovalRequest checked()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientRemovalRequest new()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientRemovalRequest whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientRemovalRequest whereApproverComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientRemovalRequest whereApproverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientRemovalRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientRemovalRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientRemovalRequest wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientRemovalRequest whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientRemovalRequest whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientRemovalRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientRemovalRequest whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientRemovalRequest canceledByTherapist()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientRemovalRequest declined()
 */
class PatientRemovalRequest extends Model implements LoggableModelInterface
{

    const STATUS_NEW = 0;
    const STATUS_ACCEPTED = 1;
    const STATUS_DECLINED = 2;
    const STATUS_CANCELED_BY_THERAPIST = 3;

    protected $guarded = [];

    protected $dates = [
        'created_at',
        'updated_at',
        'approved_at',
    ];

    protected $casts = [
        'provider_id' => 'integer',
        'patient_id' => 'integer',
        'approver_id' => 'integer',
        'status' => 'integer',

        'reason' => 'string',
        'approver_comment' => 'string',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function approver()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', static::STATUS_ACCEPTED);
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeDeclined($query)
    {
        return $query->where('status', static::STATUS_DECLINED);
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeCanceledByTherapist($query)
    {
        return $query->where('status', static::STATUS_CANCELED_BY_THERAPIST);
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeNew($query)
    {
        return $query->where('status', static::STATUS_NEW);
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeChecked($query)
    {
        return $query->whereIn('status', [static::STATUS_ACCEPTED, static::STATUS_DECLINED, static::STATUS_CANCELED_BY_THERAPIST]);
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeAddSelectStatusText($query)
    {
        return $query->addSelect(DB::raw(
            'CASE
                WHEN STATUS = ' . self::STATUS_NEW .' THEN "New"
                WHEN STATUS = ' . self::STATUS_ACCEPTED .' THEN "Accepted"
                WHEN STATUS = ' . self::STATUS_DECLINED .' THEN "Declined"
                WHEN STATUS = ' . self::STATUS_CANCELED_BY_THERAPIST .' THEN "Canceled By Therapist"
                ELSE "Unknown"
            END AS status_text')
        );
    }

    public function getDirtyWithOriginal()
    {
        $result = [];
        $dirtyFields = $this->getDirty();

        foreach ($dirtyFields as $fieldName => $fieldValue) {
            $result[$fieldName] = [
                'prev' => $this->getOriginal($fieldName),
                'curr' => $fieldValue,
            ];
        }

        return $result;
    }

    public function getLogData()
    {
        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'patient_name' => optional($this->patient)->getFullName(),
            'provider_id' => $this->provider_id,
            'provider_name' => optional($this->provider)->provider_name,
            'reason' => $this->reason,
            'approver_id' => $this->approver_id,
            'approver_name' => optional($this->approver)->email,
            'status' => $this->status,
            'approved_at' => optional($this->approved_at)->toDateTimeString(),
        ];
    }

    public function getCreateLogMessage()
    {
        return 'Patient removal request created: ' . $this->getLogMessageIdentifier();
    }

    public function getUpdateLogMessage($dirtyFields = null)
    {
        if (!isset($dirtyFields)) {
            $dirtyFields = $this->getDirtyWithOriginal();
        }

        $messagesList = [];

        foreach ($this->getScalarChangeableFields() as $fieldName => $message) {
            if (isset($dirtyFields[$fieldName])) {
                $messagesList[] = "$message changed from '{$dirtyFields[$fieldName]['prev']}' to {$dirtyFields[$fieldName]['curr']}";
            }
        }

        if (isset($dirtyFields['provider_id'])) {
            $prevProvider = Provider::find($dirtyFields['provider_id']['prev']);
            $currProvider = Provider::find($dirtyFields['provider_id']['curr']);
            $messagesList[] = "Provider id changed from '" . optional($prevProvider)->id . "' to '" . optional($currProvider)->id . "'";
            $messagesList[] = "Provider name changed from '" . optional($prevProvider)->provider_name . "' to '" . optional($currProvider)->provider_name . "'";
        }

        if (isset($dirtyFields['patient_id'])) {
            $prevPatient = Patient::find($dirtyFields['patient_id']['prev']);
            $currPatient = Patient::find($dirtyFields['patient_id']['curr']);
            $messagesList[] = "Patient id changed from '" . optional($prevPatient)->id . "' to '" . optional($currPatient)->id . "'";
            $messagesList[] = "Patient name changed from '" . optional($prevPatient)->getFullName() . "' to '"  . optional($currPatient)->getFullName() . "'";
        }

        if (isset($dirtyFields['approver_id'])) {
            $prevApprover = User::find($dirtyFields['approver_id']['prev']);
            $currApprover = User::find($dirtyFields['approver_id']['curr']);
            $messagesList[] = "Approver id changed from '" . optional($prevApprover)->id . "' to '" . optional($prevApprover)->id . "'";
            $messagesList[] = "Approver email changed from '" . optional($prevApprover)->email . "' to '" . optional($currApprover)->email . "'";
        }

        if (
            isset($dirtyFields['approved_at'])
            && Carbon::parse($dirtyFields['approved_at']['prev']) !== Carbon::parse($dirtyFields['approved_at']['curr'])
        ) {
            $messagesList[] = "Approved at changed from '{$dirtyFields['approved_at']['prev']}' to '{$dirtyFields['approved_at']['curr']}'";
        }

        return 'Patient removal request updated: ' . implode('; ', $messagesList);
    }

    public function getDeleteLogMessage()
    {
        return 'Patient removal request deleted: ' . $this->getLogMessageIdentifier();
    }

    public function getLogMessageIdentifier()
    {
        return "{$this->provider->id} - {$this->patient->id}; "
            . "{$this->provider->provider_name} - {$this->patient->getFullName()}";
    }

    public function getScalarChangeableFields()
    {
        return [
            'reason' => 'Reason',
            'approver_comment' => 'Approver comment',
            'status' => 'Status',
        ];
    }
}
