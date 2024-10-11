<?php

use App\AvailabilityType;
use Illuminate\Database\Seeder;

class AvailabilityTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = [
            ['id' => 1, 'type' => 'Regular', 'hex_color' => '#67c23a', 'input_label' => 'No'],
            ['id' => 2, 'type' => 'Additional', 'hex_color' => '#d09b00', 'input_label' => 'Yes']
        ];

        foreach ($types as $type) {
            AvailabilityType::updateOrCreate(['id' => $type['id']], $type);
        }
    }
}
