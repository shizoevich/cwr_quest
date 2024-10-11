<?php

namespace App\Jobs\Availability;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Availability;

class SaveToNewTable implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $newHW = [];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($newHW)
    {
        $this->newHW = $newHW;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->newHW as $availabilityData) {
            Availability::create($availabilityData);
        }
    }
}
