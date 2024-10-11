<?php

namespace App\Console\Commands\SingleUse;

use App\Models\Patient\DocumentRequest\PatientDocumentRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
class DublicateCreatedAtForMandrillEvent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dublicate:madrill-event-sent-at';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This console command dublicate created_at if column sent_at is empty';

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
        PatientDocumentRequest::chunk(1000, function ($requests) {
            foreach ($requests as $request) {
                $request->update([
                    'mandrill_event_id' => null,
                    'sent_at' => $request->created_at,
                    'click_at' => null,
                    'opened_at' => null
                ]);
            }
        });
    }
}
