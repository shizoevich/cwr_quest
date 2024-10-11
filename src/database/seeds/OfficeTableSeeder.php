<?php

use App\Office;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OfficeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $offices = [
            [
                'external_id' => '234627',
                'office' => 'Y Simi Valley Office',
                'tridiuum_site_id' => '0311b86e-1582-4d29-a7db-06a4c5eb0744',
                'tridiuum_is_enabled' => 1
            ],
            [
                'external_id' => '215197',
                'office' => 'Encino Office',
                'tridiuum_site_id' => '19959f55-c15d-4372-9220-37aa024c5548',
                'tridiuum_is_enabled' => 1
            ],
            [
                'external_id' => '285878',
                'office' => 'Woodland Hills',
                'tridiuum_site_id' => 'e0380174-7d9f-471e-9a95-ec112deebcab',
                'tridiuum_is_enabled' => 1
            ],
            [
                'external_id' => '6426990',
                'office' => 'New Simi Valley Office',
                'tridiuum_site_id' => '0311b86e-1582-4d29-a7db-06a4c5eb0744',
                'tridiuum_is_enabled' => 1
            ],
            [
                'external_id' => '10303560',
                'office' => 'LA Office',
                'tridiuum_site_id' => 'c7a58818-0400-49f3-8875-694e7ccdfbcd',
                'tridiuum_is_enabled' => 1
            ],
        ];

        foreach($offices as $office) {
            Office::create($office);
        }
    }   
}
