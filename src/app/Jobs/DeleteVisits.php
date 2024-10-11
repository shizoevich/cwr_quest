<?php

namespace App\Jobs;

use App\PatientVisit;
use App\Provider;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DeleteVisits implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $parsedAt;

    private $options;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($parsedAt, $options) {
        $this->parsedAt = $parsedAt;
        $this->options = $options;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $visits = PatientVisit::query();

        if(!key_exists('full-time', $this->options) || !$this->options['full-time']) {
            if(key_exists('month', $this->options) && !is_null($this->options['month'])) {
                $start = Carbon::createFromFormat('m/d/Y', $this->options['month'])->startOfMonth()->toDateString();
                $end = Carbon::createFromFormat('m/d/Y', $this->options['month'])->endOfMonth()->toDateString();
                $visits->whereDate('date', '>=', $start)->whereDate('date', '<=', $end);
            } else if(key_exists('date_range', $this->options) && !is_null($this->options['date_range'])) {
                $startDate = Carbon::parse($this->options['start_date'])->toDateString();
                $endDate = Carbon::parse($this->options['end_date'])->toDateString();
                $visits->whereDate('date', '>=', $startDate)->whereDate('date', '<=', $endDate);
            } else if(key_exists('date', $this->options) && !is_null($this->options['date'])) {
                $d = Carbon::createFromFormat('m/d/Y', $this->options['date'])->toDateString();
                $visits->whereDate('date', '=', $d);
            } else if(!key_exists('office_ally_provider_id', $this->options) || is_null($this->options['office_ally_provider_id'])) {
                $monthAgo = Carbon::now()->startOfMonth();
                $now = Carbon::now();
                $visits->whereDate('date', '>=', $monthAgo)->whereDate('date', '<=', $now);
            }

            if(key_exists('office_ally_provider_id', $this->options) && !is_null($this->options['office_ally_provider_id'])) {
                $provider = Provider::where('officeally_id', $this->options['office_ally_provider_id'])->firstOrFail();
                $visits = $provider->visits();
            }

            if(key_exists('visit_id', $this->options) && !is_null($this->options['visit_id'])) {
                $visits = PatientVisit::where('visit_id', $this->options['visit_id']);
            }
        }
        $visits->whereNotNull('parsed_at')
            ->whereNotNull('visit_id')
            ->where('parsed_at', '<', $this->parsedAt)
            ->each(function ($patientVisit) {
                $patientVisit->update([
                    'appointment_id' => null,
                    'needs_update_salary' => 1,
                    'is_overtime' => 0,
                    'deleted_at' => Carbon::now(),
                ]);
            });

    }
}
