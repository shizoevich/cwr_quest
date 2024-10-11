<?php

namespace App\Jobs\Availability;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Availability;
use App\Repositories\Provider\Availability\ProviderAvailabilityRepositoryInterface;

class UpdateRemainingLengthForAvailability implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $availability;
    private $availabilityRepository;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Availability $availability)
    {
        $this->availability = $availability;
        $this->availabilityRepository = app()->make(ProviderAvailabilityRepositoryInterface::class);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->availabilityRepository->updateRemainingLength($this->availability);
    }
}
