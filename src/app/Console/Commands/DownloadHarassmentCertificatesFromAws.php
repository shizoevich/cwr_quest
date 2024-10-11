<?php

namespace App\Console\Commands;

use App\Provider;
use App\UserMeta;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DownloadHarassmentCertificatesFromAws extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'download:harassment-certificates-from-aws';

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
        UserMeta::query()
            ->select([
                'user_id',
                'provider_id',
                'harassment_certificate_aws_name',
                \DB::raw("COALESCE(providers.provider_name, CONCAT(users_meta.firstname, ' ', users_meta.lastname)) AS user_name"),
            ])
            ->leftJoin('users', 'users.id', '=', 'users_meta.user_id')
            ->leftJoin('providers', 'providers.id', '=', 'users.provider_id')
            ->whereNotNull('harassment_certificate_aws_name')
            ->each(function ($userMeta) {
                $userName = str_replace(' ', '_', $userMeta->user_name);
                $userName = preg_replace('/,.*$/', '', $userName);

                $this->downloadFile(
                    $userName,
                    $userMeta->harassment_certificate_aws_name
                );
            });
    }

    public function downloadFile($userName, $awsDocName)
    {
        $storageName = 'harassment_certificates';
        $docName = 'harassment_certificate';
        $extension = pathinfo($awsDocName, PATHINFO_EXTENSION);

        $filename = $userName . '_' . $docName . '.' . $extension;
        $fileContent = Storage::disk($storageName)->get($awsDocName);
        Storage::disk('local')->put('temp' . '/' . 'harassment-certificates' . '/' . $filename, $fileContent);
    }
}
