<?php

namespace App\Models;

use App\PatientInsuranceProcedure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreatmentModality extends Model
{
    const DEFAULT_IN_PERSON_TREATMENT_MODALITY = 'Individual 60 min';
    const DEFAULT_TELEHEALTH_TREATMENT_MODALITY = 'Individual 60 min (Telehealth)';
    const INITIAL_EVALUATION_TREATMENT_MODALITY = 'Initial Evaluation';
    const INITIAL_EVALUATION_TELEHEALTH_TREATMENT_MODALITY = 'Initial Evaluation (Telehealth)';

    protected $fillable = [
        'insurance_procedure_id',
        'name',
        'order',
        'is_telehealth',
        'duration',
        'min_duration',
        'max_duration',
    ];

    public function insuranceProcedure(): BelongsTo
    {
        return $this->belongsTo(PatientInsuranceProcedure::class, 'insurance_procedure_id', 'id');
    }

    public static function getTreatmentModalityNameById($id)
    {
        $treatmentModality = static::find($id);
        return optional($treatmentModality)->name;
    }

    public static function getTreatmentModalityIdByName(string $name)
    {
        $treatmentModality = static::where('name', $name)->first();
        return optional($treatmentModality)->id;
    }

    public static function initialEvaluationIds()
    {
        return \Cache::rememberForever('treatment_modalities:initial_evaluation_ids', function () {
            return [
                static::getTreatmentModalityIdByName(self::INITIAL_EVALUATION_TREATMENT_MODALITY),
                static::getTreatmentModalityIdByName(self::INITIAL_EVALUATION_TELEHEALTH_TREATMENT_MODALITY),
            ];
        });
    }
}
