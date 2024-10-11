<?php

namespace App\Models\Patient;

use App\Patient;
use App\Provider;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Patient\PatientElectronicDocument
 *
 * @property int $id
 * @property int $provider_id
 * @property int $patient_id
 * @property int $reviewed_by
 * @property int $type
 * @property int $status
 * @property string $data
 * @property \Carbon\Carbon|null $reviewed_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Patient $patient
 * @property-read \App\Provider|null $provider
 * @property-read \App\User|null $reviewedBy
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientForm whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientForm whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientForm wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientForm whereReviewedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientForm whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientForm whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientForm whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientForm whereReviewedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientForm whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Patient\PatientForm whereUpdatedAt($value)
 */
class PatientForm extends Model
{
    const TYPE_NEW_PATIENT = 1;
    const TYPE_AUTHORIZATION_TO_RELEASE = 2;
    const TYPE_PICTURE = 3;
    const TYPE_TELEHEALTH = 4;
    const TYPE_PAYMENT_FOR_SERVICE = 5;

    const STATUS_NEW = 0;
    const STATUS_APPROVED = 1;
    const STATUS_DECLINED = 2;

    protected $table = 'patient_forms';

    protected $casts = [
        'id' => 'int',
        'provider_id' => 'int',
        'patient_id' => 'int',
        'type' => 'int',
        'data' => 'array',
        'reviewed_by' => 'int',
        'status' => 'int',
        'visible_in_patient_forms_page' => 'bool',
    ];
    
    protected $fillable = [
        'provider_id',
        'patient_id',
        'type',
        'data',
        'reviewed_by',
        'status',
        'visible_in_patient_forms_page',
        'reviewed_at',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'reviewed_at',
    ];

    /**
     * @return BelongsTo
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * @return BelongsTo
     */
    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    /**
     * @return BelongsTo
     */
    public function reviewedBy()
    {
        return $this->belongsTo(User::class);
    }
}
