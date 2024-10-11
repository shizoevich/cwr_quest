<?php

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageTridiuumIdSeeder extends Seeder
{
    private $languages = [
        [
            'tridiuum_id' => '25fba6b5-f309-4a8f-b266-12bb987c6ddc',
            'title' => 'English',
        ],
        [
            'tridiuum_id' => '63299218-128d-4405-ab77-b7e524c80f11',
            'title' => 'Spanish',
        ],
        [
            'tridiuum_id' => 'e845b660-6dae-41c3-8bde-60d64f11b3c0',
            'title' => 'Farsi',
        ],
        [
            'tridiuum_id' => 'dc4fb308-ce51-40e4-8212-b469bcfa2e7c',
            'title' => 'Hebrew',
        ],
        [
            'tridiuum_id' => '64fce228-27ab-431e-b7e1-1e3c19557b52',
            'title' => 'Russian',
        ],
        [
            'tridiuum_id' => 'a22d35ff-6dda-4094-9c22-68eb49036ca8',
            'title' => 'Turkish',
        ],
        [
            'tridiuum_id' => '8b6b18f2-2c26-4bf1-9c97-35b327c4fd5c',
            'title' => 'German',
        ],
        [
            'tridiuum_id' => '764e0a13-4d66-425d-b918-1fcdc7fb8d1c',
            'title' => 'Korean',
        ],
    ];

    public function run()
    {
        foreach ($this->languages as $language) {
            Language::where('title', $language['title'])->update([
                'tridiuum_id' => $language['tridiuum_id'],
            ]);
        }
    }
}
