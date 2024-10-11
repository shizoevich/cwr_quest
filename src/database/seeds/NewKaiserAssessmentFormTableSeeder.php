<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NewKaiserAssessmentFormTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('assessment_forms')->insert([
            [
                "title" => "Additional Forms",
                "document_name" => NULL,
                "file_name" => NULL,
                "type" => NULL,
                "created_at" => date("Y-m-d H:i:s"),
                "updated_at" => date("Y-m-d H:i:s"),
                "parent" => 0,
                "has_signature" => 0,
                "ind" => 18,
                "slug" => NULL,
                "group_id" => NULL,
                "password" => NULL,
            ]
        ]);

        $parent = DB::table('assessment_forms')->where('title','Additional Forms')->get()['0'];

        DB::table('assessment_forms')->insert([
            [
                "title" => "KPEP Kaiser Permanente Couples Counseling Referral",
                "document_name" => "KPEP Kaiser Permanente Couples Counseling Referral",
                "file_name" => "kpep_couples_counseling_referral.docx",
                "type" => NULL,
                "created_at" => date("Y-m-d H:i:s"),
                "updated_at" => date("Y-m-d H:i:s"),
                "parent" => $parent->id,
                "has_signature" => 0,
                "ind" => 4,
                "slug" => "kpep-couples-counseling-referral",
                "group_id" => 18,
                "password" => "CWR2015",
            ],
            [
                "title" => "KPEP Kaiser Permanente Group Referral",
                "document_name" => "KPEP Kaiser Permanente Group Referral",
                "file_name" => "kpep_group_referral.docx",
                "type" => NULL,
                "created_at" => date("Y-m-d H:i:s"),
                "updated_at" => date("Y-m-d H:i:s"),
                "parent" => $parent->id,
                "has_signature" => 0,
                "ind" => 5,
                "slug" => "kpep-group-referral",
                "group_id" => 18,
                "password" => "CWR2015",
            ],
            [
                "title" => "KPEP Kaiser Permanente Intensive Treatment Referral",
                "document_name" => "KPEP Kaiser Permanente Intensive Treatment Referral",
                "file_name" => "kpep_intensive_treatment_referral.docx",
                "type" => NULL,
                "created_at" => date("Y-m-d H:i:s"),
                "updated_at" => date("Y-m-d H:i:s"),
                "parent" => $parent->id,
                "has_signature" => 0,
                "ind" => 6,
                "slug" => "kpep-intensive-treatment-referral",
                "group_id" => 18,
                "password" => "CWR2015",
            ],
            [
                "title" => "KPEP Kaiser Permanente Medication Consultation Referral",
                "document_name" => "KPEP Kaiser Permanente Medication Consultation Referral",
                "file_name" => "kpep_medication_evaluation_referral.docx",
                "type" => NULL,
                "created_at" => date("Y-m-d H:i:s"),
                "updated_at" => date("Y-m-d H:i:s"),
                "parent" => $parent->id,
                "has_signature" => 0,
                "ind" => 7,
                "slug" => "kpep-medication-evaluation-referral",
                "group_id" => 18,
                "password" => "CWR2015",
            ],
        ]);
    }
}

