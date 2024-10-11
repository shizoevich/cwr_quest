<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class InstallNextCloudCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nextcloud:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Nextcloud configs';

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
        $cmdList = [
            'cp -R ' . resource_path('nextcloud_files') . '/* ' . config('nextcloud.nextcloud_path'),
            'cp -R ' . resource_path('loolwsd_files/config') . '/* ' . config('nextcloud.loolwsd_config'),
            'cp -R ' . resource_path('loolwsd_files/files') . '/* ' . config('nextcloud.loolwsd_files'),
        ];

        foreach ($cmdList as $cmd) {
            echo "Executing: $cmd\n";
            exec($cmd);
        }


    }
}
