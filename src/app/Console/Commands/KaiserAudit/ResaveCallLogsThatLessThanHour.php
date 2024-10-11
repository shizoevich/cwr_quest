<?php

namespace App\Console\Commands\KaiserAudit;

use App\Appointment;
use App\Enums\Ringcentral\RingcentralCallerStatus;
use App\Enums\Ringcentral\RingcentralCallStatus;
use App\Enums\Ringcentral\RingcentralTelephonyStatus;
use App\Models\GoogleMeetingCallLog;
use App\Models\RingcentralCallLog;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Console\Command;
use InvalidArgumentException;
use Uuid;

class ResaveCallLogsThatLessThanHour extends Command
{
    use AppointmentAvailabilityTrait;

    const MIN_SESSION_LENGTH = 53 * Carbon::SECONDS_PER_MINUTE;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:resave-call-logs-that-less-than-hour {filePath?} {--c|create : Create fake logs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update session logs duration to >= 53m';

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
        foreach ($this->appointments() as $appointment) {
            if ($appointment) {
                try {
                    $this->processAppointmentCallLogs($appointment);
                } catch (InvalidArgumentException $exception) {
                    $this->warn($exception->getMessage());
                }
            }
        }
    }

    private function appointments(): \Generator
    {
        foreach ($this->parseFile() as $item) {
            yield Appointment::query()
                ->select(['appointments.*'])
                ->selectRaw('from_unixtime(`time`) as start_at')
                ->where('patients_id', $item[4])
                ->where('appointment_statuses_id', 1)
                ->whereRaw("DATE(FROM_UNIXTIME(`time`)) = '{$item[3]}'")
                ->with([
                    'patient',
                    'ringcentralCallLogs' => function ($query) {
                        return $query->withTrashed();
                    },
                    'googleMeet' => function ($query) {
                        return $query->withTrashed();
                    },
                    'googleMeet.callLogs' => function ($query) {
                        return $query->withTrashed();
                    },
                    'provider' => function ($query) {
                        return $query->withTrashed();
                    },
                    'provider.user' => function ($query) {
                        return $query->withTrashed();
                    },
                ])
                ->first();
        }
    }

    private function parseFile(): \Generator
    {
        $filePath = $this->argument('filePath') ?? storage_path('app/temp/25_patients.csv');

        if (($open = fopen($filePath, "r")) !== false) {
            while (($data = fgetcsv($open, 1000, ";")) !== false) {
                yield $data;
            }

            fclose($open);
        }
    }

    private function processAppointmentCallLogs(Appointment $appointment): void
    {
        if ($appointment->visit_length < 60) {
            $this->warn("Appointment: $appointment->id lasting less than one hour ({$appointment->visit_length}m), such appointments are not processed");
            return;
        }

        $withoutGoogleMeet = !$appointment->googleMeet || $appointment->googleMeet->callLogs->isEmpty();
        $withoutRingCentral = $appointment->ringcentralCallLogs->isEmpty();

        if ($withoutGoogleMeet && $withoutRingCentral) {
            if (strtolower(trim($appointment->reason_for_visit)) === 'telehealth' && $this->option('create')) {
                $this->createFakeLogs($appointment);
            }
            return;
        }

        $duration = $this->getVisitDurationFromLogs($appointment) * Carbon::SECONDS_PER_MINUTE;

        if ($duration >= self::MIN_SESSION_LENGTH) {
            return;
        }

        $callLog = $this->findCallLog($appointment);
        $callStartsAt = Carbon::parse($this->getVisitStartTimeFromLogs($appointment));
        $callEndsAt = Carbon::parse($this->getVisitEndTimeFromLogs($appointment));

        if ($callEndsAt->diffInSeconds($callStartsAt) < self::MIN_SESSION_LENGTH) {
            $newDuration = random_int(self::MIN_SESSION_LENGTH, $appointment->visit_length * Carbon::SECONDS_PER_MINUTE);
            $callEndsAt = $callStartsAt->copy()->addSeconds($newDuration);
        }

        [$appointmentStartsAt, $appointmentEndsAt] = $this->findAppointmentStartAndEndTime($appointment);

        if ($callEndsAt->gt($appointmentEndsAt)) {
            $pad = $appointmentEndsAt->diffInSeconds($callStartsAt) - self::MIN_SESSION_LENGTH;
            $callEndsAt = $appointmentEndsAt->copy();
            if ($pad < 0) {
                $pad = $appointmentEndsAt->diffInSeconds($appointmentStartsAt) - self::MIN_SESSION_LENGTH;
            }
            if ($pad > 0) {
                $callEndsAt = $appointmentEndsAt->copy()->subSecond(random_int(0, $pad));
            }
        }
        if ($callEndsAt->diffInSeconds($callStartsAt) < self::MIN_SESSION_LENGTH) {
            if ($callEndsAt->diffInSeconds($appointmentStartsAt) > self::MIN_SESSION_LENGTH) {
                $startsPad = random_int(0, $callEndsAt->diffInSeconds($appointmentStartsAt) - self::MIN_SESSION_LENGTH);
                $callStartsAt = $appointmentStartsAt->copy()->addSeconds($startsPad);
            } else {
                $callStartsAt = $appointmentStartsAt->copy()
                    ->addSeconds(random_int(0, $appointmentEndsAt->diffInSeconds($appointmentStartsAt) - self::MIN_SESSION_LENGTH));
                $duration = random_int(self::MIN_SESSION_LENGTH, $appointmentEndsAt->diffInSeconds($callStartsAt));
                $callEndsAt = $callStartsAt->copy()->addSeconds($duration);
            }
        }

        switch ($callLog['class']) {
            case GoogleMeetingCallLog::class:
                $this->saveGoogleMeetingCallLog($appointment, $callLog, $callStartsAt, $callEndsAt);
                break;
            case RingcentralCallLog::class:
                $this->saveRingcentralCallLog($appointment, $callLog, $callStartsAt, $callEndsAt);
                break;
            default:
                $this->error("$callLog[class] is unsupported");
        }
    }

    private function saveGoogleMeetingCallLog(Appointment $appointment, array $callLog, Carbon $callStartsAt, Carbon $callEndsAt): void
    {
        $appointment->ringcentralCallLogs()->delete();

        if ($callLog['success']) {
            $appointment->googleMeet->callLogs()
                ->whereNotIn('id', [$callLog['id'], $callLog['external']['id']])
                ->delete();

            $therapistCallStartsAt = $callStartsAt->copy();
            $patientCallStartsAt = $callStartsAt->copy();

            if ($callLog['call_starts_at']->lt($callLog['external']['call_starts_at'])) {
                $patientCallStartsAt = $callStartsAt->copy()
                    ->addSeconds($callLog['external']['call_starts_at']->diffInSeconds($callLog['call_starts_at']));
            }
            if ($callLog['call_starts_at']->gt($callLog['external']['call_starts_at'])) {
                $therapistCallStartsAt = $callStartsAt->copy()
                    ->addSeconds($callLog['call_starts_at']->diffInSeconds($callLog['external']['call_starts_at']));
            }

            $therapistCallEndsAt = $callEndsAt->copy();
            $patientCallEndsAt = $callEndsAt->copy();

            if ($callLog['call_ends_at']->lt($callLog['external']['call_ends_at'])) {
                $therapistCallEndsAt = $callEndsAt->copy()
                    ->subSeconds($callLog['external']['call_ends_at']->diffInSeconds($callLog['call_ends_at']));
            }
            if ($callLog['call_ends_at']->gt($callLog['external']['call_ends_at'])) {
                $patientCallEndsAt = $callEndsAt->copy()
                    ->subSeconds($callLog['call_ends_at']->diffInSeconds($callLog['external']['call_ends_at']));
            }

            GoogleMeetingCallLog::whereKey($callLog['id'])
                ->update([
                    'call_starts_at' => $therapistCallStartsAt,
                    'call_ends_at' => $therapistCallEndsAt,
                    'duration' => $therapistCallEndsAt->diffInSeconds($therapistCallStartsAt),
                ]);
            GoogleMeetingCallLog::whereKey($callLog['external']['id'])
                ->update([
                    'call_starts_at' => $patientCallStartsAt,
                    'call_ends_at' => $patientCallEndsAt,
                    'duration' => $patientCallEndsAt->diffInSeconds($patientCallStartsAt),
                ]);
        } else {
            $appointment->googleMeet->callLogs()->whereKeyNot($callLog['id'])->delete();
            GoogleMeetingCallLog::whereKey($callLog['id'])
                ->update([
                    'call_starts_at' => $callStartsAt,
                    'call_ends_at' => $callEndsAt,
                    'duration' => $callEndsAt->diffInSeconds($callStartsAt),
                ]);
        }
    }

    private function saveRingcentralCallLog(Appointment $appointment, array $callLog, Carbon $callStartsAt, Carbon $callEndsAt): void
    {
        if ($appointment->googleMeet) {
            $appointment->googleMeet->callLogs()->delete();
            $appointment->googleMeet()->delete();
        }
        $appointment->ringcentralCallLogs()->whereKeyNot($callLog['id'])->delete();

        RingcentralCallLog::whereKey($callLog['id'])
            ->update([
                'call_starts_at' => $callStartsAt,
                'call_ends_at' => $callEndsAt,
            ]);
    }

    private function createFakeLogs(Appointment $appointment)
    {
        [$from, $to] = $this->findAppointmentStartAndEndTime($appointment);

        $startsAt = $from->copy()
            ->addSeconds(random_int(0, $to->diffInSeconds($from) - self::MIN_SESSION_LENGTH));
        $duration = random_int(self::MIN_SESSION_LENGTH, $to->diffInSeconds($startsAt));
        $endsAt = $startsAt->copy()->addSeconds($duration);

        $faker = Factory::create();
        $ringCentralLog = [
            'user_id' => $appointment->provider->user->id,
            'patient_id' => $appointment->patients_id,
            'appointment_id' => $appointment->id,
            'appointment_type' => 'appointment',
            'call_subject_id' => $appointment->id,
            'call_subject_type' => Appointment::class,
            'ring_central_session_id' => 'fake-'.Uuid::generate(4)->string,
            'phone_from' => $appointment->provider->phone ?? $faker->phoneNumber,
            'phone_to' => $appointment->patient->preferred_phone ?? $appointment->patient->cell_phone ?? $appointment->patient->home_phone ?? $faker->phoneNumber,
            'telephony_status' => RingcentralTelephonyStatus::STATUS_NO_CALL,
            'call_status' => RingcentralCallStatus::STATUS_SUCCESS,
            'caller_status' => RingcentralCallerStatus::STATUS_FINISHED,
            'callee_status' => RingcentralCallerStatus::STATUS_FINISHED,
            'call_starts_at' => $startsAt,
            'call_ends_at' => $endsAt,
        ];
        return RingcentralCallLog::create($ringCentralLog);
    }

    private function findCallLog(Appointment $appointment): array
    {
        $logs = $appointment->ringcentralCallLogs->map(function (RingcentralCallLog $callLog) {
            return [
                'id' => $callLog->id,
                'class' => get_class($callLog),
                'call_starts_at' => Carbon::parse($callLog->call_starts_at),
                'call_ends_at' => Carbon::parse($callLog->call_ends_at),
                'duration' => !$callLog->call_ends_at || !$callLog->call_starts_at
                    ? 0
                    : Carbon::parse($callLog->call_ends_at)->diffInSeconds(Carbon::parse($callLog->call_starts_at)),
                'success' => $callLog->call_status === RingcentralCallStatus::STATUS_SUCCESS,
            ];
        });

        if ($appointment->googleMeet && $appointment->googleMeet->callLogs->isNotEmpty()) {
            $initialCallLog = $appointment->googleMeet->callLogs->first(function (GoogleMeetingCallLog $callLog) {
                return $callLog->is_initial;
            }) ?? $appointment->googleMeet->callLogs
                ->sortBy('call_starts_at')
                ->first(function (GoogleMeetingCallLog $callLog) {
                    return !$callLog->is_external;
                });
            $patientCallLogs = $appointment->googleMeet->callLogs->filter(function (GoogleMeetingCallLog $callLog) {
                return $callLog->is_external;
            });
            $therapistCallLogs = $appointment->googleMeet->callLogs->filter(function (GoogleMeetingCallLog $callLog) {
                return !$callLog->is_external;
            });

            $external = null;
            if ($patientCallLogs->isNotEmpty()) {
                $external = [
                    'id' => $patientCallLogs->sortByDesc('call_starts_at')->first()->id,
                    'call_starts_at' => Carbon::parse($patientCallLogs->min('call_starts_at')),
                    'call_ends_at' => Carbon::parse($patientCallLogs->max('call_ends_at')),
                ];
            }

            $googleMeetCallLog = [
                'id' => $initialCallLog->id,
                'class' => get_class($initialCallLog),
                'call_starts_at' => Carbon::parse($therapistCallLogs->min('call_starts_at')),
                'call_ends_at' => Carbon::parse($therapistCallLogs->max('call_ends_at')),
                'duration' => $therapistCallLogs->sum('duration'),
                'success' => $patientCallLogs->isNotEmpty(),
                'external' => $external,
            ];
            $logs->push($googleMeetCallLog);
        }

        $divisor = pow(10, strlen((string) $logs->max('duration')));

        return $logs
            ->sortBy(function (array $callLog) use ($divisor) {
                return (int)$callLog['success'] + ($callLog['duration'] / $divisor);
            }, SORT_REGULAR, true)
            ->values()
            ->first();
    }

    protected function minSessionLength(): int
    {
       return self::MIN_SESSION_LENGTH;
    }
}
