<?php

namespace App\Models\Patient\Lead;

use App\Models\Patient\Comment\PatientCommentMention;
use App\UserMeta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Patient\Inquiry\PatientInquiryComment
 *
 *
 */
class PatientLeadComment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'patient_lead_id',
        'comment',
        'admin_id',
        'is_system_comment',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(UserMeta::class, 'admin_id','user_id');
    }

    public function patientLead(): BelongsTo
    {
        return $this->belongsTo(PatientLead::class, 'patient_lead_id','id');
    }

    public function mentions()
    {
        return $this->hasMany(PatientCommentMention::class, 'comment_id', 'id')->where('model', 'PatientLeadComment');
    }
}
