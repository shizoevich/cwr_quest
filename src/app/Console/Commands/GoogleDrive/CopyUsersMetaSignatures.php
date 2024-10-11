<?php

namespace App\Console\Commands\GoogleDrive;

use App\Traits\GoogleDrive\CopyUserMetaSignatureService;
use App\UserMeta;
use Illuminate\Console\Command;

class CopyUsersMetaSignatures extends Command
{
    use CopyUserMetaSignatureService;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'copy:users_signatures';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'copy users signatures from s3 to google drive';

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
        UserMeta::query()->select('user_id', 'signature', 'google_drive', 'created_at')
            ->where('google_drive', '=', false)
            ->where('deleted_at', '=', NULL)
            ->whereNotNull('signature')
            ->chunk(100, function ($userMetaData) {
                foreach ($userMetaData as $userMeta) {
                    if(
                        ($userMeta->created_at !== null) &&
                        ($userMeta->user_id !== null) &&
                        ($userMeta->signature !== null) 
                    ){
                    $this->makeCopyPatientFormDocument(
                        $userMeta->created_at,
                        $userMeta->user_id,
                        $userMeta->signature
                    );
                    UserMeta::where('user_id', $userMeta->user_id)->update(['google_drive' => true]);
                }
                }
            });
    }
}
