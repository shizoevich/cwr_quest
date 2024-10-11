<?php

namespace App\Console\Commands\OfficeAllaySingleUse;

use App\Helpers\Sites\OfficeAlly\OfficeAlly;
use Illuminate\Console\Command;

class RetryCookies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:puppeteer';

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
        $password = ''; // office ally account password
        $command = 'node ' . base_path() . '/puppeteer-scripts/' . config('officeally.login_script') . ' ' . 'groupbwt' . ' ' . $password;
        $output = [];
        $returnValue = null;
        exec($command, $output, $returnValue);

        if ($returnValue !== 0 || !count($output)) { 
            return false;
        }

        $newCookies = [];
        try {
            $oldCookies = json_decode($output[0], true);
            foreach ($oldCookies as $item) {
                $newCookies[$item['name']] = $item['value'];
            }
        } catch (\Exception $e) {
            //
        }

        if (!count($newCookies)) {
            return false;
        }
        dd($newCookies);
        // OfficeAllyCookie::query()->where('account_name', $this->accountName)->delete();
        // $cookiesModel = OfficeAllyCookie::create([
        //     'account_name' => $this->accountName,
        //     'cookies' => $newCookies,
        // ]);
        // $this->initClient($cookiesModel);
        // $this->get("Default.aspx");
        
        // return true;
    }
}
