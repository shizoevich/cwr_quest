<?php

use App\PatientComment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class UpdatePatientCommentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PatientComment::chunkById(1000, function (Collection $comments) {
            $comments->each(function (PatientComment $comment) {
                $comment->update(['comment_type' => PatientComment::DEFAULT_COMMENT_TYPE]);
            });
        });
    }
}
