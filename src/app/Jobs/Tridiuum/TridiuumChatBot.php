<?php

namespace App\Jobs\Tridiuum;

use App\Appointment;
use App\Helpers\ExceptionNotificator;
use App\Models\Parser;
use App\Notifications\AnErrorOccurred;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TridiuumChatBot implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $appointmentCount = DB::table('appointments')->where('is_created_by_tridiuum', '1')->whereDate('created_at', Carbon::yesterday()->toDateString())->count();

        $officeAllaySync = Parser::select('title', 'started_at')->get()->toArray();

        $appointmentInfo = 
        "Today was created appointment from tridiuum in the quantity ".$appointmentCount.
        " and OA parser №1 started at ".$officeAllaySync['0']['started_at'].
        " OA parser №2 started at ".$officeAllaySync['1']['started_at'].
        " OA parser №3 started at ".$officeAllaySync['2']['started_at'].
        " OA parser №4 started at ".$officeAllaySync['3']['started_at'].
        " OA parser №5 started at ".$officeAllaySync['4']['started_at'].
        " OA parser №6 started at ".$officeAllaySync['5']['started_at'];

        with(new ExceptionNotificator())->notify(new AnErrorOccurred($appointmentInfo));
    }
}

