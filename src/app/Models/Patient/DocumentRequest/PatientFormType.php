<?php

namespace App\Models\Patient\DocumentRequest;

use App\Patient;
use App\PatientDocument;
use App\PatientDocumentType;
use App\Scopes\PatientDocuments\DocumentsForAllScope;
use Illuminate\Database\Eloquent\Model;

class PatientFormType extends Model
{
    public $timestamps = false;
    
    protected $casts = [
        'order' => 'int',
        'visible_in_modal' => 'bool',
        'visible_in_tab' => 'bool',
        'is_required' => 'bool',
        'patient_can_skip_form' => 'bool',
    ];
    
    protected $fillable = [
        'name',
        'title',
        'visible_in_modal',
        'is_required',
        'patient_can_skip_form',
    ];

    public static function getIdByTitle($title)
    {
        $formType = self::where('title', $title)->first();
        return $formType ? $formType->id : null;
    }

    public static function getIdByName($name)
    {
        $formType = self::where('name', $name)->first();
        return $formType ? $formType->id : null;
    }
    
    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeVisibleInTab($query)
    {
        return $query->where('visible_in_tab', 1);
    }
    
    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', 1);
    }
    
    /**
     * @param Patient $patient
     *
     * @return bool|array
     */
    public function hasFilledDocument(Patient $patient)
    {
        if ($this->name === 'supporting_documents') {
            $insuranceTypeId = PatientDocumentType::getInsuranceSupportingDocumentId();
            $driversLicenseTypeId = PatientDocumentType::getDriversLicenseSupportingDocumentId();
            $documents = PatientDocument::query()
                ->where('patient_id', $patient->getKey())
                ->withoutGlobalScope(DocumentsForAllScope::class)
                ->whereIn('document_type_id', [$insuranceTypeId, $driversLicenseTypeId])
                ->groupBy(['document_type_id'])
                ->pluck('document_type_id')
                ->toArray();
                
            return [
                'has_insurance' => in_array($insuranceTypeId, $documents),
                'has_driver_license' => in_array($driversLicenseTypeId, $documents),
            ];
        }
        
        if (!empty($this->document_type_id)) {
            $hasFilledDocument = PatientDocument::query()
                ->where('patient_id', $patient->getKey())
                ->withoutGlobalScope(DocumentsForAllScope::class)
                ->where('document_type_id', $this->document_type_id)
                ->exists();

            if (!$hasFilledDocument && $this->name === 'payment_for_service') {
                $hasFilledDocument = $patient->formRequests()
                    ->whereHas('items', function ($query) {
                        $query->where('form_type_id', static::getIdByName('payment_for_service'))
                            ->whereNotNull('filled_at');
                    })
                    ->exists();
            }

            return $hasFilledDocument;
        }
        
        return false;
    }

    public function isSupportingDocuments() {
        return $this->name === 'supporting_documents';
    }

    public function isCreditCardOnFile() {
        return $this->name === 'credit_card_on_file';
    }

    public static function getFormTypeIds($forms) {
        return static::select('id')
            ->whereIn('name', $forms)
            ->pluck('id')
            ->toArray();
    }

    public static function getPaymentForServiceId()
    {
        return \Cache::rememberForever('form_types:payment_for_service_id', function () {
            return static::where('name', 'payment_for_service')->first()['id'];
        });
    }
}
