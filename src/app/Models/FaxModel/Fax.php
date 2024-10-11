<?php

namespace App\Models\FaxModel;

use App\Models\Patient\Lead\PatientLead;
use Illuminate\Database\Eloquent\Model;
use App\Provider;
use App\Patient;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Fax Model
 * @package App\Models\Fax;
 * 
 * @property int id
 * @property string phone
 * @property boolean is_read
 * @property integer provider_id
 * @property integer patient_id
 * @property string  uri
 * @property integer  extensionId
 * @property string  phoneNumber
 * @property string  type
 * @property string  creationTime
 * @property string  readStatus
 * @property string   priority
 * @property string   direction
 * @property string   availability
 * @property string   subject
 * @property string   messageStatus
 * @property string   faxResolution
 * @property integer faxPageCount
 *
 * @property-read \App\Provider $provider
 * @property-read \App\Patient $patient
 */
class Fax extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'phone',
        'is_read',
        'provider_id',
        'patient_id',
        'patient_lead_id',
        'uri',
        'extensionId',
        'phoneNumber',
        'type',
        'creationTime',
        'readStatus',
        'priority',
        'direction',
        'availability',
        'subject',
        'messageStatus',
        'faxResolution',
        'faxPageCount',
        'lastModifiedTime',
        'comment_id',
        'status_id',
        'file_name',
        'fax_id_webhook'
    ];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'phone' => 'string',
        'is_read' => 'boolean',
        'provider_id' => 'integer',
        'patient_id' => 'integer',
        'uri' => 'string',
        'extensionId' => 'integer',
        'phoneNumber' => 'string',
        'type' => 'string',
        'readStatus' => 'string',
        'priority' => 'string',
        'direction' => 'string',
        'availability' => 'string',
        'subject' => 'string',
        'messageStatus' => 'string',
        'faxResolution' => 'string',
        'faxPageCount' => 'integer',
        'comment_id' => 'integer',
        'status_id' => 'integer',
        'fax_id_webhook' => 'integer',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * doctor
     *
     * @return BelongsTo
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    /**
     * patient
     *
     * @return BelongsTo
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

     /**
     * patient
     *
     * @return BelongsTo
     */
    public function patientLead(): BelongsTo
    {
        return $this->belongsTo(PatientLead::class);
    }

    //filter by patient/patientLead first_name, last_name, full name
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->whereHas('patient', function ($q) use ($search) {
                $q->where('first_name', 'like', '%' . $search . '%')
                    ->orWhereRaw('CONCAT(first_name, " ", last_name) LIKE ? ', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%");
            })
                ->orWhereHas('patientLead', function ($q) use ($search) {
                    $q->where('first_name', 'like', '%' . $search . '%')
                        ->orWhereRaw('CONCAT(first_name, " ", last_name) LIKE ? ', "%{$search}%")
                        ->orWhere('last_name', 'LIKE', "%{$search}%");
                });
        });
    }

    //filter by unassigned patient
    public function scopeUnassigned($query)
    {
        return $query->where('patient_id', '!=', null);
    }

    //filter by is_read 
    public function scopeUnread($query)
    {
        return $query->where('is_read', '=', true);
    }

    // function for logging by activity log
    public function getDescriptionForEvent(string $eventName): string
    {
        return "This has status {$eventName}";
    }

    /**
     * get the comments 
     */
    public function faxLogs()
    {
        return $this->hasMany('App\Comment');
    }
}
