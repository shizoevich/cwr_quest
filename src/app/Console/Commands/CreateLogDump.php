<?php

namespace App\Console\Commands;

use App\Models\System\LogDump;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CreateLogDump extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dump:log';

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
        $latestDump = LogDump::query()->latest()->first();
        if($latestDump !== null) {
            \Schema::dropIfExists($latestDump->name);
            $latestDump->delete();
        }
        $dumpName = sprintf('log_backup_%s', Carbon::now()->format('Y_m_d__H_i_s'));
        \Schema::rename('log', $dumpName);
        LogDump::create(['name' => $dumpName]);
        \DB::statement("
          CREATE TABLE IF NOT EXISTS `log` (
            `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `data` LONGTEXT,
            `status_code` INT(11) DEFAULT NULL,
            `type` VARCHAR(8) DEFAULT NULL,
            `duration` INT(11) DEFAULT NULL,
            `url` VARCHAR(255) DEFAULT NULL,
            `client_ip` VARCHAR(255) DEFAULT NULL, PRIMARY KEY (`id`),
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=UTF8;
        ");
    }
}
