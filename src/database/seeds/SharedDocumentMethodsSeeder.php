<?php

use Illuminate\Database\Seeder;

class SharedDocumentMethodsSeeder extends Seeder
{
    private $methods = [
        'email',
        'fax',
    ];
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        array_walk($this->methods, function($method){
            \DB::table('shared_document_methods')->insert([
                'method' => $method,
            ]);
        });

    }
}
