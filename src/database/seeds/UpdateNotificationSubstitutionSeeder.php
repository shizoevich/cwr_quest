<?php

use App\Models\UpdateNotificationSubstitution;
use Illuminate\Database\Seeder;

class UpdateNotificationSubstitutionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $substitutions = [
            [
                'key' => 'full_name',
                'label' => 'Full Name',
            ],
            [
                'key' => 'email',
                'label' => 'Email',
            ]
        ];
        
        foreach ($substitutions as $substitution) {
            UpdateNotificationSubstitution::updateOrCreate(array_only($substitution, 'key'), $substitution);
        }
    }
}
