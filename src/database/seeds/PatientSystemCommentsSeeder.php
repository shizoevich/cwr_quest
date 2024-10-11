<?php

use App\PatientComment;
use App\PatientDefaultComment;
use Illuminate\Database\Seeder;

class PatientSystemCommentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $commentNames = PatientDefaultComment::all()->pluck('name');

        PatientComment::query()
            ->where('is_system_comment', true)
            ->whereNotNull('default_comment_id')
            ->whereIn('comment', $commentNames)
            ->chunk(1000, function ($comments) {
                foreach ($comments as $comment) {
                    $newComment = PatientComment::getCommentByPatientStatus($comment->comment, $comment->patient_id);
                    $comment->update([
                        'comment' => $newComment,
                        'default_comment_id' => null
                    ]);
                }
            });
    }
}
