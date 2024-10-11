<?php

namespace App\Jobs\Comments;

use App\Models\Patient\Comment\PatientCommentMention;
use App\Models\PatientHasProvider;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ParseCommentMentions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $comment;
    private $commentId;
    private $commentModel;
    private $patientId;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($comment, $commentId, $commentModel, $patientId)
    {
        $this->comment = $comment;
        $this->commentId = $commentId;
        $this->commentModel = $commentModel;
        $this->patientId = $patientId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $mentions = null;
        preg_match_all("/<span[\s\S]*data-id=\"(?<id>\d+)\"[\s\S]*>[\s\S]*<\/span>/U", $this->comment, $mentions);
        if (count($mentions['id'])) {
            foreach($mentions['id'] as $userId) {
                $user = User::withTrashed()
                    ->select(['id', 'provider_id'])
                    ->with(['roles'])
                    ->where('id', (int)$userId)
                    ->first();

                if (empty($user)) {
                    continue;
                }

                PatientCommentMention::firstOrCreate([
                    'user_id' => $user->id,
                    'comment_id' => $this->commentId,
                    'model' => $this->commentModel,
                ]);

                if ($user->isOnlyProvider()) {
                    $patientHasProviderExists = PatientHasProvider::query()
                        ->where('providers_id', $user->provider_id)
                        ->where('patients_id', $this->patientId)
                        ->exists();

                    if (!$patientHasProviderExists) {
                        PatientHasProvider::create([
                            'patients_id' => $this->patientId,
                            'providers_id' => $user->provider_id,
                            'chart_read_only' => true
                        ]);
                    }
                }
            }
        }
    }
}
