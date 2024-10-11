<?php

namespace App\Console\Commands;

use App\PatientNote;
use App\Provider;
use Illuminate\Console\Command;

class SyncPatientNoteWithProvider extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:note-with-provider';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $notes = PatientNote::whereNull('provider_id')->withTrashed()->get();

        foreach($notes as $note) {
            $providerName = $note->provider_name;
            $providerName = trim(explode(',', $providerName)[0]);
            $provider = Provider::withTrashed()
                ->where('provider_name', 'like', "%$providerName%")
                ->first();
            if(!empty($provider)) {
                $note->provider()->associate($provider);
                $note->save();
            }
        }

    }
}
