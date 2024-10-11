<?php

namespace App\Models\Patient;

use App\PatientNote;
use App\Provider;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\Patient\PatientRemoveRequest
 *
 * @property int $id
 * @property int $provider_id
 * @property int $patient_note_id
 * @property string $reason
 * @property int $approver_id
 * @property string $approver_comment
 * @property int $status
 * @property Carbon|null $approved_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $approver
 * @property-read PatientNote $patientNote
 * @property-read Provider $provider
 * @method static Builder|PatientRemovalRequest accepted()
 * @method static Builder|PatientRemovalRequest canceled()
 * @method static Builder|PatientRemovalRequest checked()
 * @method static Builder|PatientRemovalRequest new()
 * @method static Builder|PatientRemovalRequest canceledByTherapist()
 * @method static Builder|PatientRemovalRequest declined()
 * @method static Builder|PatientRemovalRequest wherePatientNoteIsEditable()
 */
class PatientNoteUnlockRequest extends Model
{
    const STATUS_NEW = 0;
    const STATUS_ACCEPTED = 1;
    const STATUS_DECLINED = 2;
    const STATUS_CANCELED_BY_THERAPIST = 3;

    protected $dates = [
        'created_at',
        'updated_at',
        'approved_at',
    ];

    protected $fillable = [
        'provider_id',
        'patient_note_id',
        'reason',
        'status',
        'approver_id',
        'approver_comment',
        'approved_at',
    ];
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function patientNote(): BelongsTo
    {
        return $this->belongsTo(PatientNote::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeAccepted($query): Builder
    {
        return $query->where('status', static::STATUS_ACCEPTED);
    }

    public function scopeDeclined($query): Builder
    {
        return $query->where('status', static::STATUS_DECLINED);
    }

    public function scopeCanceledByTherapist($query): Builder
    {
        return $query->where('status', static::STATUS_CANCELED_BY_THERAPIST);
    }

    public function scopeNew($query): Builder
    {
        return $query->where('status', static::STATUS_NEW);
    }

    public function scopeChecked($query): Builder
    {
        return $query->whereIn('status', [static::STATUS_ACCEPTED, static::STATUS_DECLINED, static::STATUS_CANCELED_BY_THERAPIST]);
    }

    public function scopeWherePatientNoteIsEditable($query): Builder
    {
        return $query->whereHas('patientNote', function ($query) {
            $query->where('is_finalized', 0)
                ->orWhere('start_editing_note_date', '>', Carbon::now()->subHours(config('app.allowed_note_editing_depth')));
        });
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
}
