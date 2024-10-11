<?php

namespace App\Models;

use App\Observers\PatientHasProviderObserver;
use App\Patient;
use App\Provider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\PatientHasProvider
 *
 * @property int $patients_id
 * @property int $providers_id
 * @property bool $chart_read_only
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientHasProvider whereChartReadOnly($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientHasProvider wherePatientsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\PatientHasProvider whereProvidersId($value)
 * @mixin \Eloquent
 */
class PatientHasProvider extends Model
{
    /**
     * @var string
     */
    public $table = 'patients_has_providers';

    /**
     * @var array
     */
    protected $fillable = [
        'patients_id',
        'providers_id',
        'chart_read_only',
        'supervisee_id',
    ];

    /**
     * @var array
     */
    protected $casts = [
      'patients_id' => 'int',
      'providers_id' => 'int',
      'chart_read_only' => 'bool',
    ];

    public function update(array $attributes = [], array $options = [])
    {
        $status = DB::table($this->table)
            ->where('patients_id', $this->patients_id)
            ->where('providers_id', $this->providers_id)
            ->update($attributes);

        if (isset($attributes['chart_read_only'])) {
            $this->chart_read_only = (int) $attributes['chart_read_only'];
        }

        if ($status) {
            $observer = new PatientHasProviderObserver();
            $observer->updated($this);
        }

        return $status;
    }

    public function delete() {
        $status = $this->patient->allProviders()->detach($this->providers_id);

        if ($status) {
            $observer = new PatientHasProviderObserver();
            $observer->deleted($this);
        }

        return $status;
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patients_id', 'id');
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class, 'providers_id', 'id');
    }

    public function getLogData()
    {
        return [
            'patients_id' => $this->patients_id,
            'providers_id' => $this->providers_id,
            'chart_read_only' => $this->chart_read_only
        ];
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

    public function getCreateLogMessage()
    {
        return 'PatientHasProvider created: ' . $this->getLogMessageIdentifier();
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

        return 'PatientHasProvider updated: ' . implode('; ', $messagesList);
    }

    public function getDeleteLogMessage()
    {
        return 'PatientHasProvider deleted: ' . $this->getLogMessageIdentifier();
    }

    public function getLogMessageIdentifier()
    {
        $providerName = optional($this->provider)->provider_name;
        $patientName = optional($this->patient)->getFullName();
        
        return "{$this->providers_id} - {$this->patients_id}; " . "{$providerName} - {$patientName}";
    }

    public function getScalarChangeableFields()
    {
        return [
            'chart_read_only' => 'Charge read only'
        ];
    }
}
