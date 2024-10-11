<?php

namespace App\Console\Commands\PatientDocsZipArchive;

use App\Helpers\Constant\DocZipArchiveConst;
use App\Models\Patient\DocumentRequest\DocumentZipArchive;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ClearZipArchiveStorage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:zip-archive-storage'; 

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove records older than 14 days from document_zip_archives.';

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
        $cutoffDate = Carbon::now()->subDays(14)->toDateString();
        $documentZipArchive = DocumentZipArchive::select('zip_file_unique_name')->where('created_at', '<', $cutoffDate)->get();

        foreach($documentZipArchive as $documentZipArchive){
            $zipFileName = $documentZipArchive->zip_file_unique_name;
            $zipFilePath = storage_path(DocZipArchiveConst::ZIP_FILE_PATH . $zipFileName);
    
            if (file_exists($zipFilePath)) {
                unlink($zipFilePath);
            }

            $documentZipArchive->delete();
        };
    }
}
