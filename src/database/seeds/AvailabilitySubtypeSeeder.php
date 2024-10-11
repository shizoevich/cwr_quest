<?php

use App\AvailabilitySubtype;
use Illuminate\Database\Seeder;

class AvailabilitySubtypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = [
            ['id' => 1, 'type' => 'Rescheduling only', 'index' => 1],
            ['id' => 2, 'type' => 'Other', 'index' => 3],
            ['id' => 3, 'type' => 'Unavailable', 'index' => 2]
        ];

        foreach ($types as $type) {
            AvailabilitySubtype::updateOrCreate(['id' => $type['id']], $type);
        }
    }
}
