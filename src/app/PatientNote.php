<?php

namespace App;

use App\Models\Diagnose;
use App\Models\Patient\PatientNoteUnlockRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\PatientNote
 *
 * @property int $id
 * @property int|null $appointment_id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $date_of_birth
 * @property string|null $provider_name
 * @property int $google_drive
 * @property string|null $provider_license_no
 * @property string|null $diagnosis_icd_code
 * @property string|null $long_range_treatment_goal
 * @property string|null $shortterm_behavioral_objective
 * @property string|null $treatment_modality
 * @property bool|null $depression
 * @property bool|null $withdrawal
 * @property bool|null $disturbed_sleep
 * @property bool|null $disturbed_eating
 * @property bool|null $tearfulness
 * @property bool|null $hopelessness
 * @property bool|null $flat_affect
 * @property bool|null $anxiety
 * @property bool|null $panic_prone
 * @property bool|null $worrisome_thinking
 * @property bool|null $phobic_avoidance
 * @property bool|null $agitated
 * @property bool|null $restless_tension
 * @property bool|null $fearfulness
 * @property bool|null $verbally_abusive
 * @property bool|null $physically_abusive
 * @property bool|null $irritable
 * @property bool|null $anger_outbursts
 * @property bool|null $disruptive_vocalizing
 * @property bool|null $interpersonal_conflict
 * @property bool|null $emotionally_labile
 * @property bool|null $impaired_reality
 * @property bool|null $delusions
 * @property bool|null $hallucinations_vis
 * @property bool|null $hallucinations_aud
 * @property bool|null $danger_to_self
 * @property bool|null $danger_to_others
 * @property bool|null $disordered_thinking
 * @property bool|null $disorientation
 * @property string|null $disorientation_status
 * @property bool|null $limited_self_expression
 * @property bool|null $limited_memory
 * @property bool|null $limited_concentration
 * @property bool|null $limited_judgment
 * @property bool|null $limited_attention
 * @property bool|null $limited_info_processing
 * @property bool|null $other_status
 * @property string|null $additional_comments
 * @property string|null $plan
 * @property string|null $interventions
 * @property string|null $progress_and_outcome
 * @property string|null $signature_degree
 * @property int $patients_id
 * @property int|null $provider_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $start_time
 * @property string|null $end_time
 * @property bool $is_finalized
 * @property string|null $finalized_at
 * @property string|null $start_editing_note_date
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $date_of_service
 * @property float $note_version
 * @property-read \App\Appointment|null $appointment
 * @property-read \Illuminate\Database\Eloquent\Collection|Diagnose[] $diagnoses
 * @property-read int|null $diagnoses_count
 * @property-read \App\Patient $patient
 * @property-read \App\Provider|null $provider
 * @property-read PatientNoteUnlockRequest|null $unlockRequest
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote onlyFinalized()
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote onlyNotFinalized()
 * @method static \Illuminate\Database\Query\Builder|PatientNote onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote query()
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereAdditionalComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereAgitated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereAngerOutbursts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereAnxiety($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereAppointmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereDangerToOthers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereDangerToSelf($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereDateOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereDateOfService($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereDelusions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereDepression($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereDiagnosisIcdCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereDisorderedThinking($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereDisorientation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereDisorientationStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereDisruptiveVocalizing($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereDisturbedEating($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereDisturbedSleep($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereEmotionallyLabile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereFearfulness($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereFinalizedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereFlatAffect($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereGoogleDrive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereHallucinationsAud($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereHallucinationsVis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereHopelessness($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereImpairedReality($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereInterpersonalConflict($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereInterventions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereIrritable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereIsFinalized($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereLimitedAttention($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereLimitedConcentration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereLimitedInfoProcessing($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereLimitedJudgment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereLimitedMemory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereLimitedSelfExpression($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereLongRangeTreatmentGoal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereNoteVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereOtherStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote wherePanicProne($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote wherePatientsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote wherePhobicAvoidance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote wherePhysicallyAbusive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote wherePlan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereProgressAndOutcome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereProviderLicenseNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereProviderName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereRestlessTension($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereShorttermBehavioralObjective($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereSignatureDegree($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereStartEditingNoteDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereTearfulness($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereTreatmentModality($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereVerballyAbusive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereWithdrawal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PatientNote whereWorrisomeThinking($value)
 * @method static \Illuminate\Database\Query\Builder|PatientNote withTrashed()
 * @method static \Illuminate\Database\Query\Builder|PatientNote withoutTrashed()
 * @mixin \Eloquent
 */
class PatientNote extends Model
{

    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $table = 'patient_notes';

    protected $guarded = [];

    protected $dates = ['deleted_at', 'created_at', 'updated_at'];

    public function patient(){
        return $this->belongsTo('App\Patient', 'patients_id', 'id');
    }

    public function provider(){
        return $this->belongsTo('App\Provider', 'provider_id', 'id');
    }

    protected $casts = [
        'depression' => 'boolean',
        'withdrawal' => 'boolean',
        'disturbed_sleep' => 'boolean',
        'disturbed_eating' => 'boolean',
        'tearfulness' => 'boolean',
        'hopelessness' => 'boolean',
        'flat_affect' => 'boolean',
        'anxiety' => 'boolean',
        'panic_prone' => 'boolean',
        'worrisome_thinking' => 'boolean',
        'phobic_avoidance' => 'boolean',
        'agitated' => 'boolean',
        'restless_tension' => 'boolean',
        'fearfulness' => 'boolean',
        'verbally_abusive' => 'boolean',
        'physically_abusive' => 'boolean',
        'irritable' => 'boolean',
        'anger_outbursts' => 'boolean',
        'disruptive_vocalizing' => 'boolean',
        'interpersonal_conflict' => 'boolean',
        'emotionally_labile' => 'boolean',
        'impaired_reality' => 'boolean',
        'delusions' => 'boolean',
        'hallucinations_vis' => 'boolean',
        'hallucinations_aud' => 'boolean',
        'danger_to_self' => 'boolean',
        'danger_to_others' => 'boolean',
        'disordered_thinking' => 'boolean',
        'disorientation' => 'boolean',
        'limited_self_expression' => 'boolean',
        'limited_memory' => 'boolean',
        'limited_concentration' => 'boolean',
        'limited_judgment' => 'boolean',
        'limited_attention' => 'boolean',
        'limited_info_processing' => 'boolean',
        'other_status' => 'boolean',
        'is_finalized' => 'boolean',

        'id' => 'integer',
        'patients_id' => 'integer',
        'provider_id' => 'integer',
        'appointment_id' => 'integer',
    ];

    public function scopeOnlyFinalized($query) {
        return $query->where('is_finalized', '=', true);
    }
    
    public function scopeOnlyNotFinalized($query) {
        return $query->where('is_finalized', '=', false);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function diagnoses()
    {
        return $this->belongsToMany(Diagnose::class, 'patient_note_diagnoses');
    }

    public function syncDiagnoses(array $newDiagnosesIds) {
        foreach($newDiagnosesIds as $id) {
            $patientNoteDiagnose = PatientNoteDiagnoses::where('patient_note_id', $this->id)
                ->where('diagnose_id', $id)
                ->first();
            
            if (!$patientNoteDiagnose) {
                PatientNoteDiagnoses::create(['patient_note_id' => $this->id, 'diagnose_id' => $id]);
            }
        }

        $patientNoteDiagnosesToDelete = PatientNoteDiagnoses::where('patient_note_id', $this->id)
            ->whereNotIn('diagnose_id', $newDiagnosesIds)->get();

        foreach($patientNoteDiagnosesToDelete as $diagnose) {
            $diagnose->delete();
        }
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
            'appointment_id' => $this->appointment_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'date_of_birth' => $this->date_of_birth,
            'provider_name' => $this->provider_name,
            'google_drive' => $this->google_drive,
            'provider_license_no' => $this->provider_license_no,
            'diagnosis_icd_code' => $this->diagnosis_icd_code,
            'long_range_treatment_goal' => $this->long_range_treatment_goal,
            'shortterm_behavioral_objective' => $this->shortterm_behavioral_objective,
            'treatment_modality_id' => $this->treatment_modality_id,
            'treatment_modality' => $this->treatment_modality,
            'depression' => $this->depression,
            'withdrawal' => $this->withdrawal,
            'disturbed_sleep' => $this->disturbed_sleep,
            'disturbed_eating' => $this->disturbed_eating,
            'tearfulness' => $this->tearfulness,
            'hopelessness' => $this->hopelessness,
            'flat_affect' => $this->flat_affect,
            'anxiety' => $this->anxiety,
            'panic_prone' => $this->panic_prone,
            'worrisome_thinking' => $this->worrisome_thinking,
            'phobic_avoidance' => $this->phobic_avoidance,
            'agitated' => $this->agitated,
            'restless_tension' => $this->restless_tension,
            'fearfulness' => $this->fearfulness,
            'verbally_abusive' => $this->verbally_abusive,
            'physically_abusive' => $this->physically_abusive,
            'irritable' => $this->irritable,
            'anger_outbursts' => $this->anger_outbursts,
            'disruptive_vocalizing' => $this->disruptive_vocalizing,
            'interpersonal_conflict' => $this->interpersonal_conflict,
            'emotionally_labile' => $this->emotionally_labile,
            'impaired_reality' => $this->impaired_reality,
            'delusions' => $this->delusions,
            'hallucinations_vis' => $this->hallucinations_vis,
            'hallucinations_aud' => $this->hallucinations_aud,
            'danger_to_self' => $this->danger_to_self,
            'danger_to_others' => $this->danger_to_others,
            'disordered_thinking' => $this->disordered_thinking,
            'disorientation' => $this->disorientation,
            'disorientation_status' => $this->disorientation_status,
            'limited_self_expression' => $this->limited_self_expression,
            'limited_memory' => $this->limited_memory,
            'limited_concentration' => $this->limited_concentration,
            'limited_judgment' => $this->limited_judgment,
            'limited_attention' => $this->limited_attention,
            'limited_info_processing' => $this->limited_info_processing,
            'other_status' => $this->other_status,
            'additional_comments' => $this->additional_comments,
            'plan' => $this->plan,
            'interventions' => $this->interventions,
            'progress_and_outcome' => $this->progress_and_outcome,
            'signature_degree' => $this->signature_degree,
            'patients_id' => $this->patients_id,
            'provider_id' => $this->provider_id,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'is_finalized' => $this->is_finalized,
            'finalized_at' => $this->finalized_at,
            'start_editing_note_date' => $this->start_editing_note_date,
            'date_of_service' => $this->date_of_service,
        ];
    }

    public function getCreateLogMessage()
    {
        return 'Note created: ' . $this->getLogMessageIdentifier();
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

        return 'Note updated: ' . implode('; ', $messagesList);
    }

    public function getDeleteLogMessage()
    {
        return 'Note deleted: ' . $this->getLogMessageIdentifier();
    }

    public function getRestoreLogMessage()
    {
        return 'Note restored: ' . $this->getLogMessageIdentifier();
    }

    public function getLogMessageIdentifier()
    {
        return "{$this->id}; {$this->appointment_id}; '{$this->first_name} {$this->last_name}'; '{$this->provider_name}'"
            . Carbon::parse($this->created_at)->toDateTimeString();
    }

    public function getScalarChangeableFields()
    {
        return [
            'appointment_id' => 'Appointment id',
            'first_name' => 'First name',
            'last_name' => 'Last name',
            'provider_name' => 'Provider name',
            'google_drive' => 'Google Drive',
            'provider_license_no' => 'Provider license number',
            'diagnosis_icd_code' => 'Diagnosis ICD code',
            'long_range_treatment_goal' => 'Long-range treatment goal',
            'shortterm_behavioral_objective' => 'Short-term behavioral objective',
            'treatment_modality' => 'Treatment modality',
            'depression' => 'Depression',
            'withdrawal' => 'Withdrawal',
            'disturbed_sleep' => 'Disturbed sleep',
            'disturbed_eating' => 'Disturbed eating',
            'tearfulness' => 'Tearfulness',
            'hopelessness' => 'Hopelessness',
            'flat_affect' => 'Flat affect',
            'anxiety' => 'Anxiety',
            'panic_prone' => 'Panic prone',
            'worrisome_thinking' => 'Worrisome thinking',
            'phobic_avoidance' => 'Phobic avoidance',
            'agitated' => 'Agitated',
            'restless_tension' => 'Restless tension',
            'fearfulness' => 'Fearfulness',
            'verbally_abusive' => 'Verbally abusive',
            'physically_abusive' => 'Physically abusive',
            'irritable' => 'Irritable',
            'anger_outbursts' => 'Anger outbursts',
            'disruptive_vocalizing' => 'Disruptive vocalizing',
            'interpersonal_conflict' => 'Interpersonal conflict',
            'emotionally_labile' => 'Emotionally labile',
            'impaired_reality' => 'Impaired reality',
            'delusions' => 'Delusions',
            'hallucinations_vis' => 'Hallucinations (visual)',
            'hallucinations_aud' => 'Hallucinations (auditory)',
            'danger_to_self' => 'Danger to self',
            'danger_to_others' => 'Danger to others',
            'disordered_thinking' => 'Disordered thinking',
            'disorientation' => 'Disorientation',
            'disorientation_status' => 'Disorientation status',
            'limited_self_expression' => 'Limited self-expression',
            'limited_memory' => 'Limited memory',
            'limited_concentration' => 'Limited concentration',
            'limited_judgment' => 'Limited judgment',
            'limited_attention' => 'Limited attention',
            'limited_info_processing' => 'Limited information processing',
            'other_status' => 'Other status',
            'additional_comments' => 'Additional comments',
            'plan' => 'Plan',
            'interventions' => 'Interventions',
            'progress_and_outcome' => 'Progress and outcome',
            'signature_degree' => 'Signature degree',
            'patients_id' => 'Patients ID',
            'provider_id' => 'Provider ID',
            'start_time' => 'Start time',
            'end_time' => 'End time',
            'is_finalized' => 'Is finalized',
            'finalized_at' => 'Finalized at',
            'start_editing_note_date' => 'Start editing note date',
            'date_of_service' => 'Date of service',
        ];
    }

    public function unlockRequest(): HasOne
    {
        return $this->hasOne(PatientNoteUnlockRequest::class)->new();
    }

    public function isEditable(): bool
    {
        return !$this->is_finalized || Carbon::parse($this->start_editing_note_date)
            ->gte(Carbon::now()->subHours(config('app.allowed_note_editing_depth')));
    }
}
