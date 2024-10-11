<?php

namespace App\Console\Commands;

use App\PatientDocument;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DeleteUnknownDocuments extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'documents:delete_unknown';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete documents without document type.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $documents = PatientDocument::where('visible', false)
            ->where('document_type_id', null)
            ->where('deleted_at', null)
            ->whereRaw("DATEDIFF('" . Carbon::now() . "', `created_at`) > 0")
            ->get();
        $deleted_docs = [];
        foreach($documents as $doc) {
            $deleted_docs[] = $doc->toArray();
            Storage::disk('patients_docs')->delete($doc->aws_document_name);
            $doc->forceDelete();
        }
        if(count($deleted_docs)) {
            Log::info('Deleted documents without document type: ', $deleted_docs);
        }
    }
}
