<?php

namespace App\Jobs\Tridiuum;

use App\Helpers\TridiuumHelper;
use App\Models\TridiuumProvider;
use App\Provider;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GetProviders extends AbstractParser
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->connection = 'redis_long';
        $this->queue = 'tridiuum';

        parent::__construct();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handleParser()
    {
        if (!config('parser.tridiuum.enabled')) {
            return;
        }
        
        $tridiuumHelper = new TridiuumHelper();
        $providers = $tridiuumHelper->getProviders();
        if(!is_array($providers)) {
            return;
        }
        $parsedAt = Carbon::now();
        foreach ($providers as $item) {
            $practName = trim(str_replace('(Change Within Reach)', '', $item['pract_name']));
            $nameParts = explode(',', $practName);
            if(count($nameParts) < 2) {
                return;
            }
            $provider = TridiuumProvider::query()->updateOrCreate(['external_id' => $item['id']], [
                'name' => $practName,
                'first_name' => trim($nameParts[1]),
                'last_name' => trim($nameParts[0]),
                'parsed_at' => $parsedAt
            ]);
            if(config('tridiuum.enable_auto_assign_providers') && $provider->internal_id === null && $provider->custom_reassigned_at === null) {
                $this->tryAssignProvider($provider);
            }
        }
        if(count($providers) > 0) {
            TridiuumProvider::query()->where('parsed_at', '<', $parsedAt)->delete();
        }
    }
    
    private function tryAssignProvider(TridiuumProvider $provider)
    {
        $providerName = $provider->first_name . ' ' . $provider->last_name;
        $providers = Provider::query()
            ->whereDoesntHave('tridiuumProvider')
            ->where('provider_name', 'like', "{$providerName}%")
            ->get();
        if($providers->count() !== 1) {
            return;
        }
        $provider->internal_id = $providers->first()->getKey();
        $provider->save();
    }
}
