<?php

namespace App\Console\Commands\KaiserAudit;

use App\Appointment;
use App\Status;
use Illuminate\Console\Command;

class FindCancelledAppointments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:find-cancelled-appointments';

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
        $filename = "25_patients.csv";
        $path = storage_path('app/temp/' . $filename);
        $array = [];

        if (($open = fopen($path, "r")) !== false) {
            while (($data = fgetcsv($open, 1000, ";")) !== false) {
                $array[] = $data;
            }
         
            fclose($open);
        }
        
        foreach ($array as $item) {
            $canceledAppt = Appointment::withTrashed()
                ->where('patients_id', $item[4])
                ->whereRaw("DATE(FROM_UNIXTIME(`time`)) = '{$item[3]}'")
                ->where(function ($query) {
                    $query->whereNotNull('deleted_at')
                        ->orWhereIn('appointment_statuses_id', Status::getOtherCancelStatusesId());
                })
                ->first();
            $completedAppt = Appointment::query()
                ->where('patients_id', $item[4])
                ->where('appointment_statuses_id', 1)
                ->whereRaw("DATE(FROM_UNIXTIME(`time`)) = '{$item[3]}'")
                ->first();

            if (isset($canceledAppt) && !isset($completedAppt)) {
                dump($item);
                if (isset($canceledAppt->deleted_at)) {
                    dump('DELETED_APPT: ' . $canceledAppt->id);
                } else {
                    dump('CANCELED_APPT: ' . $canceledAppt->id);
                }
                dump('---------------------------------------');
            }
        }
    }
}
