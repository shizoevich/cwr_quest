<?php

use App\Models\Billing\BillingPeriodType;
use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $languages = [
            [
                'slug' => 'english',
                'title' => 'English',
            ],
            [
                'slug' => 'spanish',
                'title' => 'Spanish',
            ]
        ];
        
        foreach ($languages as $language) {
            Language::query()->updateOrCreate(array_only($language, 'slug'), $language);
        }
    }
}
