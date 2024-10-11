<?php

use App\Models\Therapist\TherapistSurveyRace;
use Illuminate\Database\Seeder;

class TherapistRaceSeeder extends Seeder
{
    private $races = [
        [
            'tridiuum_value' => 'e77ece36-6d4a-4469-bb61-1ee6862678f0',
            'label' => 'American Indian or Alaskan Native'
        ],
        [
            'tridiuum_value' => '23deaf27-3b38-4d86-8651-86a0643755fa',
            'label' => 'Asian'
        ],
        [
            'tridiuum_value' => 'bee56a4d-52e8-4373-a4c4-62f94b012ce9',
            'label' => 'Asian/Pacific Islander - Asian Indian'
        ],
        [
            'tridiuum_value' => 'af730d5b-bbe2-42ed-b1ed-b9921073271c',
            'label' => 'Asian/Pacific Islander - Bangladeshi'
        ],
        [
            'tridiuum_value' => '4b0c2e21-f3f3-4a58-a1a8-b8809ec523e3',
            'label' => 'Asian/Pacific Islander - Cambodian'
        ],
        [
            'tridiuum_value' => '0f846712-0fcc-403a-9809-f737b2605605',
            'label' => 'Asian/Pacific Islander - Chinese'
        ],
        [
            'tridiuum_value' => '695ad0d3-0a65-47d1-8639-d03880b4885d',
            'label' => 'Asian/Pacific Islander - East Indian'
        ],
        [
            'tridiuum_value' => 'c29dd529-1b27-4394-936b-f10eaae439d8',
            'label' => 'Asian/Pacific Islander - Fijian'
        ],
        [
            'tridiuum_value' => 'ff4d2705-acda-4e33-ab8e-98fd6e4c07a0',
            'label' => 'Asian/Pacific Islander - Filipino'
        ],
        [
            'tridiuum_value' => '2edce850-90ec-493d-b2f9-260fede8d26e',
            'label' => 'Asian/Pacific Islander - Guamanian or Chamorro'
        ],
        [
            'tridiuum_value' => '85e64c84-29dd-481b-b2d3-042f31075de4',
            'label' => 'Asian/Pacific Islander - Hmong'
        ],
        [
            'tridiuum_value' => '38f79a15-fbfb-4207-a17d-34187d399006',
            'label' => 'Asian/Pacific Islander - Indonesian'
        ],
        [
            'tridiuum_value' => 'eca967a1-bde4-44c0-896c-1a44c7f0ed32',
            'label' => 'Asian/Pacific Islander - Japanese'
        ],
        [
            'tridiuum_value' => '4549d4d5-0edb-4895-a7ea-18dddfdc58a9',
            'label' => 'Asian/Pacific Islander - Korean'
        ],
        [
            'tridiuum_value' => '7188a538-e198-4d18-a744-3e932f7b056a',
            'label' => 'Asian/Pacific Islander - Laotian'
        ],
        [
            'tridiuum_value' => '09f6f270-3c78-4413-bb40-78cd8b379d78',
            'label' => 'Asian/Pacific Islander - Malaysian'
        ],
        [
            'tridiuum_value' => '1cb3b039-4c75-4321-a477-8f3ee192e517',
            'label' => 'Asian/Pacific Islander - Maori'
        ],
        [
            'tridiuum_value' => '917d34c8-3653-48d2-8b08-c59ae07d9209',
            'label' => 'Asian/Pacific Islander - Melanesian'
        ],
        [
            'tridiuum_value' => '594334f9-2300-43c2-9658-1365f6ed081d',
            'label' => 'Asian/Pacific Islander - Native Hawaiian'
        ],
        [
            'tridiuum_value' => '1dbd256e-e2ee-4a9c-9aed-fff457b36daf',
            'label' => 'Asian/Pacific Islander - Nepalese'
        ],
        [
            'tridiuum_value' => '2ec69838-31c6-4b39-aad3-ae54d53ea216',
            'label' => 'Asian/Pacific Islander - Other Asian'
        ],
        [
            'tridiuum_value' => 'b3864456-ae97-41cd-8fbf-624ea57030c9',
            'label' => 'Asian/Pacific Islander - Other Pacific Islander'
        ],
        [
            'tridiuum_value' => '1cb0011f-84de-48a1-8f13-0b5a058643e8',
            'label' => 'Asian/Pacific Islander - Other South Asian/East Indian'
        ],
        [
            'tridiuum_value' => '30823662-ed69-4807-91b1-3d043fcf6781',
            'label' => 'Asian/Pacific Islander - Other Southeast Asian'
        ],
        [
            'tridiuum_value' => '7ec7e3ba-4baa-418b-be10-0c38994b32c5',
            'label' => 'Asian/Pacific Islander - Pakistani'
        ],
        [
            'tridiuum_value' => '98def38e-eed3-494c-a900-c50c64e8a811',
            'label' => 'Asian/Pacific Islander - Part Hawaiian'
        ],
        [
            'tridiuum_value' => '56e8262a-25a1-49fc-aa0d-84e0144ad1c5',
            'label' => 'Asian/Pacific Islander - Samoan'
        ],
        [
            'tridiuum_value' => 'cfd2da02-0d09-4391-a6d1-e1eea3918349',
            'label' => 'Asian/Pacific Islander - Sri Lankan'
        ],
        [
            'tridiuum_value' => '7c50e493-5670-45fd-af24-41d74c018a62',
            'label' => 'Asian/Pacific Islander - Tahitian'
        ],
        [
            'tridiuum_value' => 'dfd60796-8e7d-478b-be1b-1d06bd879f9a',
            'label' => 'Asian/Pacific Islander - Thai'
        ],
        [
            'tridiuum_value' => 'fdbefa74-784f-4c94-94a8-8597e77daf5d',
            'label' => 'Asian/Pacific Islander - Tongan'
        ],
        [
            'tridiuum_value' => '2ddd1218-04aa-4170-b4b2-8ab5a06a9341',
            'label' => 'Asian/Pacific Islander - Vietnamese'
        ],
        [
            'tridiuum_value' => '4f2a8ed0-4704-429d-a072-7af90b9de37a',
            'label' => 'Black - Caribbean'
        ],
        [
            'tridiuum_value' => '522123e4-6c3a-4c06-b2da-19c575354ce1',
            'label' => 'Black - Egyptian'
        ],
        [
            'tridiuum_value' => '299ddc64-5c2c-4196-845d-efe885d10f4a',
            'label' => 'Black - Native African'
        ],
        [
            'tridiuum_value' => '173df9be-2a3e-4e62-ad59-f147695dedc7',
            'label' => 'Black or African American'
        ],
        [
            'tridiuum_value' => 'd41bbb03-487c-4949-a711-5c06a3236c38',
            'label' => 'Black - Other Black'
        ],
        [
            'tridiuum_value' => '33efaaad-441d-484a-b139-7c453c8ee6cf',
            'label' => 'Hispanic'
        ],
        [
            'tridiuum_value' => 'ade8599e-8cf0-4a57-9e14-5b2328e2ac40',
            'label' => 'Native American/Eskimo/Aleutian - Aleutian'
        ],
        [
            'tridiuum_value' => '25b82003-4b54-4e54-bff5-4eb20e6c0877',
            'label' => 'Native American/Eskimo/Aleutian - American Indian'
        ],
        [
            'tridiuum_value' => '63f39103-5df4-4a05-b97b-b04a6c9f4338',
            'label' => 'Native American/Eskimo/Aleutian - Eskimo'
        ],
        [
            'tridiuum_value' => 'd4e644a9-cdd7-427c-a232-02bbaaedfc79',
            'label' => 'Native American/Eskimo/Aleutian - Other Native American/Eskimo/Aleutian'
        ],
        [
            'tridiuum_value' => 'f251ad61-5812-4dbf-95a6-c7e16eab2a14',
            'label' => 'Native Hawaiian or Other Pacific Islander'
        ],
        [
            'tridiuum_value' => '83ddefe2-0dd7-4541-906b-469662e15afa',
            'label' => 'Other'
        ],
        [
            'tridiuum_value' => '51035847-a5a7-474b-9437-4ab6c911f13b',
            'label' => 'Refused'
        ],
        [
            'tridiuum_value' => '2b4439e6-e816-4628-bfbc-c8583e313eb6',
            'label' => 'Unknown'
        ],
        [
            'tridiuum_value' => '9187fc8b-2af2-448a-99e8-543c2ed21eec',
            'label' => 'Vietnamese'
        ],
        [
            'tridiuum_value' => '77b86e25-dcc3-4806-be71-d18313d6277a',
            'label' => 'White'
        ],
        [
            'tridiuum_value' => 'ae1c9bbd-c471-4423-b12c-62d720b3ee53',
            'label' => 'White - Eastern European Bosnian'
        ],
        [
            'tridiuum_value' => 'c943416d-d116-4ba7-9e06-5262066f550f',
            'label' => 'White - Eastern European Croatian'
        ],
        [
            'tridiuum_value' => 'cb2da9d1-81a8-420f-aff4-685e30422549',
            'label' => 'White - Eastern European Other Eastern European'
        ],
        [
            'tridiuum_value' => '552562af-4f5b-437a-9548-329aca732497',
            'label' => 'White - Eastern European Russian'
        ],
        [
            'tridiuum_value' => '5ef6c50c-8f2b-41d8-8d15-ce8d28291195',
            'label' => 'White - Eastern European Serbian'
        ],
        [
            'tridiuum_value' => '997ccff2-80cc-42fe-8206-84ad018fb9cd',
            'label' => 'White - Middle Eastern/Arab Algerian'
        ],
        [
            'tridiuum_value' => 'f6406f3c-0912-4c58-b3c3-765f1f5c35cc',
            'label' => 'White - Middle Eastern/Arab Armenian'
        ],
        [
            'tridiuum_value' => 'ef80be61-845a-499f-8e72-29e4fd0ffb36',
            'label' => 'White - Middle Eastern/Arab Iranian'
        ],
        [
            'tridiuum_value' => '21c2ccc1-5bb8-49b7-b92b-ab9cdea376a2',
            'label' => 'White - Middle Eastern/Arab Iraq'
        ],
        [
            'tridiuum_value' => '624c18f0-8bbd-42f2-a709-9734f053f4f9',
            'label' => 'White - Middle Eastern/Arab Israeli'
        ],
        [
            'tridiuum_value' => 'd68e745e-50f8-4ffd-a6ac-f8d894a6192c',
            'label' => 'White - Middle Eastern/Arab Jordanian'
        ],
        [
            'tridiuum_value' => 'eafbe0ce-abc5-4e4d-9a81-660bad12b507',
            'label' => 'White - Middle Eastern/Arab Kuwaiti'
        ],
        [
            'tridiuum_value' => 'ea62fb42-67ac-4077-91e9-b47f1de76590',
            'label' => 'White - Middle Eastern/Arab Lebanese'
        ],
        [
            'tridiuum_value' => 'aa395310-e43b-4a71-9316-84e6f9f781ae',
            'label' => 'White - Middle Eastern/Arab Libyan'
        ],
        [
            'tridiuum_value' => 'db73c7b4-a836-44df-9183-9ee2a33f4220',
            'label' => 'White - Middle Eastern/Arab Other Middle Eastern European/Arab'
        ],
        [
            'tridiuum_value' => '6ed92372-50d4-456c-b659-6edb6a3d54c3',
            'label' => 'White - Middle Eastern/Arab Palestinian'
        ],
        [
            'tridiuum_value' => 'b9e6a43c-6125-45a0-8d65-83f6904dc0ee',
            'label' => 'White - Middle Eastern/Arab Saudi Arabian'
        ],
        [
            'tridiuum_value' => '9a9d1683-ea3f-4507-9eb7-13dee300ff0e',
            'label' => 'White - Middle Eastern/Arab Syrian'
        ],
        [
            'tridiuum_value' => '537aa3ec-8f36-474f-8e45-72aed5c6b97b',
            'label' => 'White - Middle Eastern/Arab Tunisian'
        ],
        [
            'tridiuum_value' => '7813628e-c281-40d6-a717-bef22f494ba2',
            'label' => 'White - Middle Eastern/Arab Turkish'
        ],
        [
            'tridiuum_value' => '6a31871f-c285-4fdd-aef2-4537f315800b',
            'label' => 'White - Middle Eastern/Arab Yemen'
        ],
        [
            'tridiuum_value' => '8024db2a-56b9-426d-84e6-5839eeaa873d',
            'label' => 'White - Middle Eastern Egyptian'
        ],
        [
            'tridiuum_value' => '6b9bc078-ba14-493d-bf85-2701bd80e79d',
            'label' => 'White - Other White or European'
        ],
        [
            'tridiuum_value' => 'c98f6d04-00e4-40a4-91dd-4b916c3543ca',
            'label' => 'White - Western European'
        ]
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TherapistSurveyRace::query()->delete();

        TherapistSurveyRace::insert($this->races);
    }
}
