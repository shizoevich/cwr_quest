<?php

namespace App\Console\Commands\GoogleDrive;

use App\Models\FaxModel\Fax;
use App\Traits\GoogleDrive\CopyFaxDocumentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CopyFaxes extends Command
{
    use CopyFaxDocumentService;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'copy:faxes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'copy faxes from s3 to google drive';

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
        Fax::query()->select('id', 'google_drive', 'creationTime', 'file_name')
            ->where('google_drive', '=', false)
            ->whereMonth('creationTime', '=', date('m'))
            ->orderBy('id', 'desc')
            ->chunk(10, function ($faxes) {
                foreach ($faxes as $fax) {
                    if (($fax->id !== null) && ($fax->creationTime !== null) && ($fax->file_name !== null)) {
                        if (Storage::disk('faxes')->exists($fax->file_name)) {
                            $this->makeCopyFaxDocument($fax->id, $fax->creationTime, $fax->file_name);
                        } else {
                            Fax::query()->where('file_name', $fax->file_name)->update(['google_drive' => true]);
                        }
                    }
                }
            });
    }
}
