<?php

namespace App\Jobs\Comments;

use App\Models\Patient\Comment\PatientCommentMention;
use App\PatientComment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WriteCommentWithMention implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $comment;
    protected $patientId;
    protected $userId;
    protected $adminId;
    protected $mentionModel;

    /**
     * WriteCommentWithMention constructor.
     *
     * @param string $comment
     * @param int $patientId
     * @param int $userId
     * @param int $adminId
     * @param string $mentionModel
     */
    public function __construct(
        string $comment,
        int    $patientId,
        int    $userId,
        int    $adminId,
        string $mentionModel = 'PatientComment'
    )
    {
        $this->comment = $comment;
        $this->patientId = $patientId;
        $this->userId = $userId;
        $this->adminId = $adminId;
        $this->mentionModel = $mentionModel;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        $patientComment = PatientComment::create([
            'is_system_comment' => true,
            'comment' => $this->comment,
            'admin_id' => $this->adminId,
            'patient_id' => $this->patientId,
        ]);

        PatientCommentMention::firstOrCreate([
            'user_id' => $this->userId,
            'comment_id' => $patientComment->id,
            'model' => $this->mentionModel,
        ]);
    }
}
