<?php

use Illuminate\Database\Seeder;

class SharedDocumentStatusesSeeder extends Seeder
{
    private $statuses = [
        'sent',
        'delivered',
        'dropped',
        'queue',
        'opened',
    ];
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        array_walk($this->statuses, function($status){
            \DB::table('shared_document_statuses')->insert([
                'status' => $status,
            ]);
        });

    }
}
