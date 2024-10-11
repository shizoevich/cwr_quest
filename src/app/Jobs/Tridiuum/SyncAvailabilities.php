<?php

namespace App\Jobs\Tridiuum;

use App\Availability;
use App\AvailabilityType;
use App\Helpers\TridiuumHelper;
use App\Jobs\Availability\GetDoctorsAvailabilityGroupedByOffice;
use App\Models\TridiuumProvider;
use App\Office;
use App\Provider;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class SyncAvailabilities extends AbstractParser
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var null|int|int[] */
    protected $providerId;

    /**
     * Create a new job instance.
     *
     * @param null|int|int[] $providerId
     */
    public function __construct($providerId = null)
    {
        $this->providerId = $providerId;

        $this->onQueue('tridiuum-availability');

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

        Provider::query()
            ->select('providers.*')
            ->when($this->providerId, function (Builder $query, $providerId) {
                $query->whereKey($providerId);
            })
            ->where('tridiuum_sync_availability', 1)
            ->whereHas('tridiuumProvider')
            ->each(function (Provider $provider) {
                $this->syncProviderData($provider);
            });
    }

    /**
     * @param Provider $provider
     */
    protected function syncProviderData(Provider $provider)
    {
        $start = Carbon::yesterday()->startOfDay();
        foreach (CarbonPeriod::create($start, '1 week', $start->copy()->addWeeks(8)) as $startDate) {
            $endDate = $startDate->copy()->addWeek();
            $data = [
                "provider_id" => $provider->getKey(),
                "office_id" => "0",
                "age_group_id_all" => [0 => "0"],
                "types_of_clients_id_all" => [0 => "0"],
                "insurances_id_all" => [0 => "0"],
                "languages" => [0 => "0"],
                "practice_focus_id_all" => [0 => "0"],
                "visit_types" => [0 => "0"],
                "insurance_id" => "0",
                "start" => $startDate->toIso8601String(),
                "end" => $endDate->toIso8601String(),
            ];

            $this->syncAvailabilitiesData($provider, $startDate, $endDate, $data);
        }
    }

    /**
     * @param Provider $provider
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param array $data
     */
    protected function syncAvailabilitiesData(Provider $provider, Carbon $startDate, Carbon $endDate, $data)
    {
        if (!optional($provider->tridiuumProvider)->external_id) {
            return;
        }

        $tridiuumHelper = new TridiuumHelper();
        $offices = Office::all();

        $currentAvailabilities = \Bus::dispatchNow(new GetDoctorsAvailabilityGroupedByOffice($startDate, $endDate, $data, true));
        $tridiuumAvailabilities = $tridiuumHelper->getAvailability($startDate, $endDate, $provider->tridiuumProvider->external_id);
        if ($tridiuumAvailabilities === false) {
            return;
        }
        $toCreate = collect();
        $toDelete = $tridiuumAvailabilities->reject(function ($tridiuumAvailability) {
            return Carbon::parse(data_get($tridiuumAvailability, 'end_date'))->lt(Carbon::now());
        })->pluck('id');

        foreach ($currentAvailabilities as $currentAvailability) {
            $currentAvailabilityStart = Carbon::parse(data_get($currentAvailability, 'start'));
            $currentAvailabilityEnd = Carbon::parse(data_get($currentAvailability, 'end'));

            $info = data_get($currentAvailability, 'info', []);
            foreach ($info as $infoItem) {
                $infoItemData = array_first($infoItem, null, []);

                $availabiliyTypeId = data_get($infoItemData, 'availability_type_id');
                if ($availabiliyTypeId === AvailabilityType::getForRescheduleId()) {
                    continue;
                }

                $officeId = data_get($infoItemData, 'office_id');
                /** @var Office|null $office */
                $office = $offices->first(function (Office $office) use ($officeId) {
                    return $office->getKey() === $officeId;
                });

                if ($office && $office->tridiuum_is_enabled) {
                    $actualTridiuumAvailability = $tridiuumAvailabilities->first(function ($tridiuumAvailability) use ($office, $currentAvailabilityStart, $currentAvailabilityEnd, $infoItemData) {
                        $siteId = data_get($tridiuumAvailability, 'site_id');
                        $tridiuumAvailabilityStart = Carbon::parse(data_get($tridiuumAvailability, 'start_date'));
                        $tridiuumAvailabilityEnd = Carbon::parse(data_get($tridiuumAvailability, 'end_date'));

                        return $office->tridiuum_site_id === $siteId
                            && $tridiuumAvailabilityStart->eq($currentAvailabilityStart)
                            && $tridiuumAvailabilityEnd->eq($currentAvailabilityEnd)
                            && data_get($tridiuumAvailability, 'in_person') == data_get($infoItemData, 'in_person')
                            && data_get($tridiuumAvailability, 'virtual') == data_get($infoItemData, 'virtual');
                    });

                    if ($actualTridiuumAvailability) {
                        $toDelete = $toDelete->reject(function ($item) use ($actualTridiuumAvailability) {
                            return $item === data_get($actualTridiuumAvailability, 'id');
                        });
                    } else {
                        $toCreate->push([
                            'item' => array_except($currentAvailability, ['info']),
                            'info' => $infoItemData,
                            'office' => $office
                        ]);
                    }
                }
            }
        }

        foreach ($toDelete as $toDeleteId) {
            rescue(function () use ($tridiuumHelper, $toDeleteId) {
                $tridiuumHelper->tridiuum->deleteAvailability($toDeleteId);
            });
        }

        foreach ($toCreate as $toCreateItem) {
            $item = data_get($toCreateItem, 'item');
            $from = Carbon::parse(data_get($item, 'start'));
            $to = Carbon::parse(data_get($item, 'end'));
            $virtual = data_get($toCreateItem, 'info.virtual');
            $inPerson = data_get($toCreateItem, 'info.in_person');
            $siteId = data_get($toCreateItem, 'office.tridiuum_site_id');

            rescue(
                function () use ($tridiuumHelper, $siteId, $from, $to, $provider, $virtual, $inPerson) {
                    $tridiuumHelper->tridiuum->createAvailability($siteId, $from, $to, $provider->tridiuumProvider->external_id, $virtual, $inPerson);
                },
                function ()  use ($siteId, $from, $to, $provider, $virtual, $inPerson) {

                    Availability::where('provider_id', TridiuumProvider::where('external_id', $provider->tridiuumProvider->external_id)->first()->internal_id)
                        ->where('start_date', date('Y-m-d', strtotime($from)))
                        ->where('start_time', date('H:i:s', strtotime($from)))
                        ->each(function ($availability) {
                            $availability->delete();
                        });
                }
            );
        }
    }
}
