<?php

namespace App\Jobs\Notifications;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\Twilio\TwilioLookup;
use App\Services\Ringcentral\RingcentralSms as RingcentralSmsService;

class RingcentralSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var string */
    protected $to;
    
    /** @var string */
    protected $message;

    /**
     * Create a new job instance.
     *
     * @param string $to
     * @param string $message
     */
    public function __construct(string $to, string $message)
    {
        $this->to = $to;
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return array
     */
    public function handle()
    {
        try {
            TwilioLookup::validatePhone($this->to);

            $smsService = new RingcentralSmsService();
            $smsService->store([$this->to], $this->message);

            return [
                'status' => true,
                'message' => __('messages.successful_sending'),
            ];
        } catch (\Exception $e) {
            \App\Helpers\SentryLogger::captureException($e);
            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
