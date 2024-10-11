<?php

namespace App\Console\Commands;

use App\Exceptions\Email\EmailNotSentException;
use App\Exceptions\Email\EmailInRejectListException;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\ScheduledNotification;
/**
 * @see https://github.com/thomasjohnkane/snooze
 * Class SendScheduledNotifications
 * @package App\Console\Commands
 */
class SendScheduledNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snooze:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send scheduled notifications that are ready to be sent.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $tolerance = config('snooze.sendTolerance');

        $notifications = ScheduledNotification::whereNull('sent_at')
                                ->whereNull('cancelled_at')
                                ->where('send_at', '<=', Carbon::now())
                                ->where('send_at', '>=', Carbon::now()->subSeconds($tolerance ?? 60))
                                ->get();

        if (! $notifications->count()) {
            $this->info('No Scheduled Notifications need to be sent.');

            return;
        }

        $this->info('Starting Sending Scheduled Notifications');

        $bar = $this->output->createProgressBar(count($notifications));

        $bar->start();

        $this->info(sprintf('Sending %d scheduled notifications...', $notifications->count()));

        $notifications->each(function (ScheduledNotification $notification) use ($bar) {
            $bar->advance();

            try {
                $notification->send();
            } catch (EmailNotSentException | EmailInRejectListException $e) {
                $notification->cancel(ScheduledNotification::EMAIL_NOT_SENT_EXCEPTION_REASON_ID);
                \App\Helpers\SentryLogger::captureException($e);
            } catch (\Exception $e) {
                \Log::error($e->getTraceAsString());
                $this->error($e->getMessage());
                \App\Helpers\SentryLogger::captureException($e);
            }
        });

        $bar->finish();

        $this->info('Finished Sending Scheduled Notifications');
    }
}
