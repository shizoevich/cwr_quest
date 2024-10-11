<?php

namespace App;

use App\Models\Square\SquareTransaction;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * App\PatientSquareAccount
 *
 * @property int $id
 * @property int|null $patient_id
 * @property string|null $external_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PatientSquareAccountCard[] $cards
 * @property-read \App\Patient $patient
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientSquareAccount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientSquareAccount whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientSquareAccount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientSquareAccount wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientSquareAccount whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Square\SquareTransaction[] $transactions
 */
class PatientSquareAccount extends Model
{
    protected $fillable = [
        'external_id',
        'patient_id',
        'first_name',
        'last_name',
        'email',
    ];

    public function patient()
    {
        return $this->hasOne(Patient::class);
    }

    public function cards()
    {
        return $this->hasMany(PatientSquareAccountCard::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions()
    {
        return $this->hasMany(SquareTransaction::class, 'customer_id', 'id');
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeUnattached($query)
    {
        return $query->whereNull('patient_id');
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
            'external_id' => $this->external_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email
        ];
    }

    public function getCreateLogMessage()
    {
        return 'Square account created: ' . $this->getLogMessageIdentifier();
    }

    public function getUpdateLogMessage($dirtyFields = null)
    {
        if (empty($dirtyFields)) {
            $dirtyFields = $this->getDirtyWithOriginal();
        }

        $messagesList = [];

        foreach ($this->getScalarChangeableFields() as $fieldName => $message) {
            if (isset($dirtyFields[$fieldName])) {
                $messagesList[] = "$message changed from '{$dirtyFields[$fieldName]['prev']}' to '{$dirtyFields[$fieldName]['curr']}'";
            }
        }

        return 'Square account updated: ' . implode('; ', $messagesList);
    }

    public function getDeleteLogMessage()
    {
        return 'Square account deleted: ' . $this->getLogMessageIdentifier();
    }

    public function getLogMessageIdentifier()
    {
        return "{$this->id}; {$this->patient_id}; "
            . Carbon::parse($this->created_at)->toDateTimeString();
    }

    public function getScalarChangeableFields()
    {
        return [
            'patient_id' => 'Patient id',
            'external_id' => 'External id',
            'first_name' => 'First name',
            'last_name' => 'Last name',
            'email' => 'Email'
        ];
    }
}
