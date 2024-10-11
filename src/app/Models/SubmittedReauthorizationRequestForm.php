<?php

namespace App\Models;

use App\Patient;
use App\User;
use App\PatientDocument;
use App\Models\Patient\PatientElectronicDocument;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubmittedReauthorizationRequestForm extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'document_id',
        'document_type',
        'patient_id',
        'submitted_by',
        'stage_id',
        'comment',
        'stage_changed_at',
    ];

    public function document(): MorphTo
    {
        return $this->morphTo();
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(SubmittedReauthorizationRequestFormStage::class, 'stage_id', 'id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'id');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by', 'id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(SubmittedReauthorizationRequestFormLog::class, 'form_id');
    }

    public function futureInsuranceReauthorizationData(): HasOne
    {
        return $this->hasOne(FutureInsuranceReauthorizationData::class, 'form_id');
    }

    public function stageChangeHistory(): HasMany
    {
        return $this->hasMany(SubmittedReauthorizationRequestFormStageChangeHistory::class, 'form_id');
    }

    public static function getSubmittedBy($document)
    {
        $documentClass = get_class($document);

        $submittedBy = null;
        if ($documentClass === PatientDocument::class) {
            $submittedBy = optional($document->uploadInfo)->user_id;
        }
        if ($documentClass === PatientElectronicDocument::class) {
            $submittedBy = optional($document->provider->user)->id;
        }

        return $submittedBy;
    }
}
