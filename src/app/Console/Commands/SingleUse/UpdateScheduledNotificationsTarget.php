<?php

namespace App\Console\Commands\SingleUse;

use Illuminate\Console\Command;
use App\Models\ScheduledNotification;
use Carbon\Carbon;

class UpdateScheduledNotificationsTarget extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scheduled-notifications:update-target {--date=}';

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
        $date = $this->option('date') ?? Carbon::today()->toDateString();

        ScheduledNotification::query()
            ->whereNull('sent_at')
            ->whereNull('cancelled_at')
            ->where('target', 'LIKE', '%twilio_sms%')
            ->whereDate('send_at', '>=', $date)
            ->each(function ($notification) {
                $notification->update(['target' => str_replace('s:10:"twilio_sms"', 's:15:"ringcentral_sms"', $notification->target)]);
            });
    }
}
