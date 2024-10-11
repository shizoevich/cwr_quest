<?php

use App\Models\Therapist\TherapistSurveyLanguage;
use App\TherapistSurvey;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TherapistLanguageSeeder extends Seeder
{
    private $languages = [
        [
            "tridiuum_value" => "4c17becb-3c56-4121-b923-c969fed9d4f8",
            "label" => "American Sign Language"
        ],
        [
            "tridiuum_value" => "af817c63-4e6d-4658-ba2f-0cd4dee86bd7",
            "label" => "Amharic"
        ],
        [
            "tridiuum_value" => "0b65ebf5-0334-4bb1-8671-22936d6b216d",
            "label" => "Arabic"
        ],
        [
            "tridiuum_value" => "b532db3e-1df6-4a83-b92d-983b2d2147c2",
            "label" => "Armenian"
        ],
        [
            "tridiuum_value" => "af309db2-4fd9-4fef-a76f-f9b92c8d6bce",
            "label" => "Bengali"
        ],
        [
            "tridiuum_value" => "06c4b1b3-f938-487a-a928-4175d74e9c8c",
            "label" => "Bulgarian"
        ],
        [
            "tridiuum_value" => "2c4a5144-e07b-47e6-8ce5-28d20596798b",
            "label" => "Burmese"
        ],
        [
            "tridiuum_value" => "89eb0e60-da7a-499d-b4fc-ba6238046020",
            "label" => "Chamorro"
        ],
        [
            "tridiuum_value" => "a9ba4363-eb35-4d95-b94c-f6ad8e94409e",
            "label" => "Chinese"
        ],
        [
            "tridiuum_value" => "69e88f45-b828-47a4-a987-9754fe63ccde",
            "label" => "Chinese Cantonese"
        ],
        [
            "tridiuum_value" => "63b98586-fb1f-4d94-b60e-7f22cf490942",
            "label" => "Croatian"
        ],
        [
            "tridiuum_value" => "c4d1fd65-3981-4b46-a116-bd6cf69b8bd3",
            "label" => "Dutch"
        ],
        [
            "tridiuum_value" => "25fba6b5-f309-4a8f-b266-12bb987c6ddc",
            "label" => "English"
        ],
        [
            "tridiuum_value" => "e845b660-6dae-41c3-8bde-60d64f11b3c0",
            "label" => "Farsi"
        ],
        [
            "tridiuum_value" => "1a78f166-6d3b-4380-991d-b577c3be0e24",
            "label" => "French"
        ],
        [
            "tridiuum_value" => "8b6b18f2-2c26-4bf1-9c97-35b327c4fd5c",
            "label" => "German"
        ],
        [
            "tridiuum_value" => "c5bdbd0e-aeea-4825-8ef6-622f9c3fec99",
            "label" => "Greek"
        ],
        [
            "tridiuum_value" => "e6e58fa9-c7df-4536-841d-8504be1bb59d",
            "label" => "Gujarati"
        ],
        [
            "tridiuum_value" => "51d9f209-e101-4147-b8da-9c8ade68987d",
            "label" => "Hakka"
        ],
        [
            "tridiuum_value" => "a1544a91-f511-41d9-9133-acdb865ea0eb",
            "label" => "Hatian,Creole"
        ],
        [
            "tridiuum_value" => "dc4fb308-ce51-40e4-8212-b469bcfa2e7c",
            "label" => "Hebrew"
        ],
        [
            "tridiuum_value" => "33891963-5a3e-4d3f-b4f3-2578010b7b0c",
            "label" => "Hindi"
        ],
        [
            "tridiuum_value" => "93c2e3d9-7432-49ef-b8a2-48b7069dcfa6",
            "label" => "Hmong"
        ],
        [
            "tridiuum_value" => "a10860b5-4934-41b5-8673-6ee83df6b66d",
            "label" => "Hungarian"
        ],
        [
            "tridiuum_value" => "b7bb9dd6-a097-41fb-ab53-2a69db515788",
            "label" => "Ilocano"
        ],
        [
            "tridiuum_value" => "822b3d03-b613-43e2-bf5c-7a712ec83525",
            "label" => "Indonesian"
        ],
        [
            "tridiuum_value" => "8639012a-ecbe-4cf4-b8f8-b43b37aac238",
            "label" => "Italian"
        ],
        [
            "tridiuum_value" => "8920eb1d-b7fa-45b9-ba58-5f18b229dd71",
            "label" => "Japanese"
        ],
        [
            "tridiuum_value" => "9a34e54b-e4da-4826-95d8-bda9940904c4",
            "label" => "Khmer"
        ],
        [
            "tridiuum_value" => "764e0a13-4d66-425d-b918-1fcdc7fb8d1c",
            "label" => "Korean"
        ],
        [
            "tridiuum_value" => "7c52e30c-dfb2-4229-8aea-940f75959786",
            "label" => "Laotian"
        ],
        [
            "tridiuum_value" => "b25a3e9d-f81c-4fe3-8bfb-caf97849af8a",
            "label" => "Lithuanian"
        ],
        [
            "tridiuum_value" => "1979064e-2da6-45f8-8b80-9d63e44a080e",
            "label" => "Malayalam"
        ],
        [
            "tridiuum_value" => "ba4f9aaa-e4ec-40d6-9be8-7dfb006110a8",
            "label" => "Mien"
        ],
        [
            "tridiuum_value" => "07eef119-b66e-4275-a052-e5cbc9c37644",
            "label" => "Miscellaneous"
        ],
        [
            "tridiuum_value" => "6ea695c5-fc3c-45b5-870d-323aaae4f66f",
            "label" => "Native American Indian"
        ],
        [
            "tridiuum_value" => "c9cf3d7d-1211-45e5-b979-27907c4883fa",
            "label" => "Other"
        ],
        [
            "tridiuum_value" => "c20531d2-31dd-439f-a5e1-c79c25a83ece",
            "label" => "Pashto"
        ],
        [
            "tridiuum_value" => "56e81877-a99e-455b-be1c-1c6c0e92f53d",
            "label" => "Polish"
        ],
        [
            "tridiuum_value" => "6ebc1d61-44d6-4908-a312-ce3f6cbeacd5",
            "label" => "Portuguese"
        ],
        [
            "tridiuum_value" => "b2f52393-bcec-4a78-98fb-974e4f078e5e",
            "label" => "Punjabi"
        ],
        [
            "tridiuum_value" => "33dc0e85-49a4-467d-8bcd-83f896ae51d7",
            "label" => "Romanian"
        ],
        [
            "tridiuum_value" => "64fce228-27ab-431e-b7e1-1e3c19557b52",
            "label" => "Russian"
        ],
        [
            "tridiuum_value" => "546e11f9-06a9-4990-83d0-6464ff1b8cc1",
            "label" => "Samoan"
        ],
        [
            "tridiuum_value" => "39432da8-b983-4a5e-970d-66c51bf2a7c0",
            "label" => "sanskrith"
        ],
        [
            "tridiuum_value" => "a4a14e34-143f-41e5-9540-efad85ff8caf",
            "label" => "Serbian"
        ],
        [
            "tridiuum_value" => "0a6c22bf-38f1-43cb-bef1-631c339687b8",
            "label" => "Shanghainese"
        ],
        [
            "tridiuum_value" => "8d95c457-00d8-44d0-b3eb-5a46c0bdda5c",
            "label" => "Somali"
        ],
        [
            "tridiuum_value" => "63299218-128d-4405-ab77-b7e524c80f11",
            "label" => "Spanish"
        ],
        [
            "tridiuum_value" => "e440123c-0943-4c27-8d55-9cfb2b9f03c6",
            "label" => "Tagalog"
        ],
        [
            "tridiuum_value" => "209829e8-0c39-4912-b969-8f7b2573543a",
            "label" => "Taiwanese"
        ],
        [
            "tridiuum_value" => "759f11b4-c132-46c9-bcea-3dd45b642db9",
            "label" => "Tamil"
        ],
        [
            "tridiuum_value" => "e8498961-2c21-4246-a589-3a455c5821eb",
            "label" => "Thai"
        ],
        [
            "tridiuum_value" => "5051d280-d102-4d57-94fe-406bfc77f41b",
            "label" => "Tigrinya"
        ],
        [
            "tridiuum_value" => "efc8dd95-cd6f-4dfb-b38f-5023c03c3585",
            "label" => "Toishanese"
        ],
        [
            "tridiuum_value" => "0defa0c4-7850-4660-910d-820d7daddf2c",
            "label" => "Tongan"
        ],
        [
            "tridiuum_value" => "a22d35ff-6dda-4094-9c22-68eb49036ca8",
            "label" => "Turkish"
        ],
        [
            "tridiuum_value" => "37af6bfa-7454-450b-a6fd-c7854a261822",
            "label" => "Ukranian"
        ],
        [
            "tridiuum_value" => "e633f3fd-5f87-485a-b61f-8a1d1a0ed011",
            "label" => "Unknown"
        ],
        [
            "tridiuum_value" => "56ee4450-9ae5-45ae-ba60-54615375af9c",
            "label" => "Urdu"
        ],
        [
            "tridiuum_value" => "27c7d87b-54a2-4cfe-b49b-b53d6c2d8f03",
            "label" => "Vietnamese"
        ],
        [
            "tridiuum_value" => "439e61b6-4655-495b-a628-39f0e1a40af6",
            "label" => "Visayan"
        ]
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        TherapistSurveyLanguage::query()->delete();

        TherapistSurveyLanguage::insert($this->languages);

        // if (Schema::hasColumn('therapist_survey', 'languages')) {
            $languages = TherapistSurveyLanguage::query()
                ->select(['id', 'label'])
                ->get();

            $therapists = TherapistSurvey::all();

            $therapists->each(function ($therapist) use ($languages) {
                $therapistLanguagesStr = strtolower($therapist->languages);

                $languages->each(function ($language) use ($therapist, $therapistLanguagesStr) {
                    if (Str::contains($therapistLanguagesStr, strtolower($language->label))) {
                        $therapist->languagesTridiuum()->attach($language->id);
                    }
                });
            });
        // }
    }
}
