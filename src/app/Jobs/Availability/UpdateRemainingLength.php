<?php

namespace App\Jobs\Availability;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Availability;
use App\Repositories\Provider\Availability\ProviderAvailabilityRepositoryInterface;

class UpdateRemainingLength implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $date;
    private $providerId;
    private $availabilityRepository;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($date, $providerId)
    {
        $this->date = $date;
        $this->providerId = $providerId;
        $this->availabilityRepository = app()->make(ProviderAvailabilityRepositoryInterface::class);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $availabilities = $this->getAvailabilities();
        foreach ($availabilities as $availability) {
            $this->availabilityRepository->updateRemainingLength($availability);
        }
    }

    private function getAvailabilities()
    {
        return Availability::query()
            ->where('provider_id', '=', $this->providerId)
            ->whereDate('start_date', '=', $this->date)
            ->get();
    }
}
