<?php

namespace App\Models\Provider;

use Illuminate\Database\Eloquent\Model;
use App\Provider;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProviderSupervisor extends Model
{
    protected $fillable = [
        'provider_id',
        'supervisor_id',
        'attached_at',
        'detached_at',
    ];

    protected $casts = [
        'attached_at' => 'datetime',
        'detached_at' => 'datetime',
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(Provider::class, 'supervisor_id');
    }

    public static function getSupervisorForDate($providerId, Carbon $date)
    {
        return ProviderSupervisor::query()
            ->where('provider_id', $providerId)
            ->whereDate('attached_at', '<=', $date->toDateString())
            ->where(function ($query) use (&$date) {
                $query->whereNull('detached_at')
                    ->orWhereDate('detached_at', '>', $date->toDateString());
            })
            ->first();
    }

    public static function getSuperviseeForToday($supervisorId)
    {
        $today = Carbon::today()->toDateString();

        return ProviderSupervisor::query()
            ->select([
                'provider_supervisors.*',
                'providers.provider_name',
                DB::raw('(CASE WHEN users.deleted_at IS NULL THEN 1 ELSE 0 END) AS is_active')
            ])
            ->where('supervisor_id', $supervisorId)
            ->whereDate('attached_at', '<=', $today)
            ->where(function ($query) use (&$today) {
                $query->whereNull('detached_at')
                ->orWhereDate('detached_at', '>', $today);
            })
            ->join('providers', function ($join) {
                $join->on('providers.id', '=', 'provider_supervisors.provider_id')
                ->whereNull('providers.deleted_at');
            })
            ->join('users', 'users.provider_id', '=', 'provider_supervisors.provider_id')
            ->get();
    }

    public static function getSuperviseesForPeriod($supervisorId, Carbon $startDate, Carbon $endDate)
    {
        return ProviderSupervisor::query()
            ->select('provider_supervisors.*', 'providers.provider_name')
            ->where('supervisor_id', $supervisorId)
            ->whereDate('attached_at', '<=', $endDate->toDateString())
            ->where(function ($query) use (&$startDate) {
                $query->whereNull('detached_at')
                    ->orWhereDate('detached_at', '>=', $startDate->toDateString());
            })
            ->where(function ($query) use ($startDate) {
                $query->whereNull('users.deleted_at')
                      ->orWhereDate('users.deleted_at', '>=', $startDate->toDateString());
            })
            ->join('providers', 'providers.id', '=', 'provider_supervisors.provider_id')
            ->join('users', 'users.provider_id', '=', 'provider_supervisors.provider_id')
            ->groupBy('provider_supervisors.provider_id')
            ->get();
    }
}
