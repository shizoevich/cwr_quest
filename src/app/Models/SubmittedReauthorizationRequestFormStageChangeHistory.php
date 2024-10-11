<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubmittedReauthorizationRequestFormStageChangeHistory extends Model
{
    use SoftDeletes;

    protected $table = 'submitted_reauthorization_request_form_stage_change_history';

    protected $fillable = [
        'form_id',
        'old_stage_id',
        'new_stage_id',
        'user_id',
        'comment',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(SubmittedReauthorizationRequestForm::class, 'form_id', 'id');
    }

    public function oldStage(): BelongsTo
    {
        return $this->belongsTo(SubmittedReauthorizationRequestFormStage::class, 'old_stage_id', 'id');
    }

    public function newStage(): BelongsTo
    {
        return $this->belongsTo(SubmittedReauthorizationRequestFormStage::class, 'new_stage_id', 'id');
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
