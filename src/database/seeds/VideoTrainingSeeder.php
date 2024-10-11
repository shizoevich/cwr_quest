<?php

use App\Models\VideoTraining;
use Illuminate\Database\Seeder;

class VideoTrainingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->getData()->each(function($item, $key) {
            VideoTraining::query()->updateOrCreate([
                'slug' => $item['slug']
            ], $item + ['index' => $key]);
        });
    }
    
    private function getData()
    {
        return collect([
            [
                'slug' => 'video_1',
                'title' => 'David Kessler On Grief and Grieving (Digital Seminar)',
                'description' => '',
                'source_url' => 'https://cwr-video-trainings.s3-us-west-1.amazonaws.com/1_2019_02_19david-kessler-grief-gr_360pAAC_640x360_700.mp4',
                'source_type' => 'video/mp4',
                'duration' => 19997,
            ],
            [
                'slug' => 'video_2',
                'title' => 'Module 1 | David Kessler: Finding Meaning: The Sixth Stage of Grief',
                'description' => '',
                'source_url' => 'https://cwr-video-trainings.s3-us-west-1.amazonaws.com/2_1_josie-video-1_1080pAAC_1920x1080_2500.mp4',
                'source_type' => 'video/mp4',
                'duration' => 6599,
            ],
            [
                'slug' => 'video_3',
                'title' => 'Module 2 | David Kessler: Finding Meaning: The Sixth Stage of Grief',
                'description' => '',
                'source_url' => 'https://cwr-video-trainings.s3-us-west-1.amazonaws.com/2_2_josie-video-2_1080pAAC_1920x1080_2500.mp4',
                'source_type' => 'video/mp4',
                'duration' => 4106,
            ],
            [
                'slug' => 'video_4',
                'title' => 'Module 3 | David Kessler: Finding Meaning: The Sixth Stage of Grief',
                'description' => '',
                'source_url' => 'https://cwr-video-trainings.s3-us-west-1.amazonaws.com/2_3_josie-video-3_1080pAAC_1920x1080_2500.mp4',
                'source_type' => 'video/mp4',
                'duration' => 4982,
            ],
            [
                'slug' => 'video_5',
                'title' => 'Module 4 | David Kessler: Finding Meaning: The Sixth Stage of Grief',
                'description' => '',
                'source_url' => 'https://cwr-video-trainings.s3-us-west-1.amazonaws.com/2_4_josie-video-4_1080pAAC_1920x1080_2500.mp4',
                'source_type' => 'video/mp4',
                'duration' => 3997,
            ],
            [
                'slug' => 'video_6',
                'title' => 'Using IFS to Advance Trauma Therapy with Couples and Families: Coming Full Circle (Digital Seminar)',
                'description' => '',
                'source_url' => 'https://cwr-video-trainings.s3-us-west-1.amazonaws.com/3_2017_07_21pesi-ifschrismp4_720pAAC_1280x720_1200.mp4',
                'source_type' => 'video/mp4',
                'duration' => 16384,
            ],
            [
                'slug' => 'video_7',
                'title' => 'Anxiety and Relationships in the New Era (Digital Seminar)',
                'description' => '',
                'source_url' => 'https://cwr-video-trainings.s3-us-west-1.amazonaws.com/4_voices-d-julie-gottman_720pAAC_1280x720_1600.mp4',
                'source_type' => 'video/mp4',
                'duration' => 3785,
            ],
            [
                'slug' => 'video_8',
                'title' => 'The Gottman Method Approach to Better Couples Therapy (Digital Seminar)',
                'description' => '',
                'source_url' => 'https://cwr-video-trainings.s3-us-west-1.amazonaws.com/5_095929_the-magic-trio_360pAAC_640x360_700.mp4',
                'source_type' => 'video/mp4',
                'duration' => 7163,
            ],
            [
                'slug' => 'video_9',
                'title' => 'Maximize Telehealth: Tapping into Your Clients World to Improve Therapeutic Outcomes (Digital Seminar)',
                'description' => '',
                'source_url' => 'https://cwr-video-trainings.s3-us-west-1.amazonaws.com/6_2020_06_30maximizetelehealthrnv0_720pAAC_1280x720_1600.mp4',
                'source_type' => 'video/mp4',
                'duration' => 5308,
            ],
        ]);
    }
}
