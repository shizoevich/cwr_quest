<?php

use App\AvailabilitySubtype;
use App\Models\Patient\PatientTag;
use App\Models\Patient\PatientTransfer;
use App\Patient;
use App\PatientStatus;
use App\Provider;
use App\Status;
use App\UserMeta;
use Illuminate\Database\Seeder;
use Symfony\Component\DomCrawler\Crawler;

class PatientTransferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $baseQuery = Patient::query()
            ->whereDoesntHave('transfers', function ($query) {
                $query->active();
            })
            ->whereDoesntHave('providers', function ($query) {
                $query->withTrashed();
            })
            ->where('created_at', '>', now()->subYear())
            ->where(function ($whereQuery) {
                $whereQuery
                    ->whereNotIn('status_id', [PatientStatus::getLostId(), PatientStatus::getArchivedId()])
                    ->orWhere(function ($orWhereQuery) {
                        $orWhereQuery
                            ->whereHas('comment', function ($commentQuery) {
                                $commentQuery
                                    ->where('is_system_comment', 1)
                                    ->where(function ($whereQuery) {
                                        $whereQuery
                                            ->where('comment', 'like', '%unassigned%')
                                            ->orWhere('comment', 'like', '%removed the patient from the list of%');
                                    });
                            })
                            ->whereHas('appointments', function ($appointmentsQuery) {
                                $appointmentsQuery->whereIn('appointment_statuses_id', Status::getCompletedVisitCreatedStatusesId());
                            });
                    });
            });

        dump('Patients that need transfers: ' . $baseQuery->count());
        $number = 1;

        $baseQuery
            ->with([
                'comment' => function ($query) {
                    $query
                        ->where('is_system_comment', 1)
                        ->where(function ($whereQuery) {
                            $whereQuery
                                ->where('comment', 'like', '%unassigned%')
                                ->orWhere('comment', 'like', '%removed the patient from the list of%');
                        })
                        ->orderBy('created_at', 'desc');
                }
            ])
            ->chunkById(100, function ($patients) use (&$number) {
                $patients->each(function ($patient) use (&$number) {
                    if ($patient->comment) {
                        $crawler = new Crawler($patient->comment->comment);

                        $adminName = $crawler->filter('.label-blue.bold')->first()->text();
                        $providerName = $crawler->filter('.label-blue.bold')->last()->text();

                        if (str_contains($patient->comment->comment, 'automatically')) {
                            $admin = null;
                        } else {
                            [$adminFirstname, $adminLastname] = explode(' ', $adminName, 2);

                            $admin = UserMeta::query()
                                ->withTrashed()
                                ->where('firstname', $adminFirstname)
                                ->where('lastname', $adminLastname)
                                ->first();
                        }

                        $provider = Provider::query()
                            ->withTrashed()
                            ->where('provider_name', $providerName)
                            ->first();

                        PatientTransfer::create([
                            'patient_id' => $patient->id,
                            'old_provider_id' => optional($provider)->id,
                            'created_by' => optional($admin)->user_id,
                            'closed_at' => null,
                            'unassigned_at' => $patient->comment->created_at,
                        ]);

                        $dumpString = $number . ' | Patient: ' . $patient->id . ', ' . $patient->first_name . ' '
                            . $patient->last_name . '; '
                            . 'Provider: ' . optional($provider)->id . ', ' . $providerName . ';';

                    } else {
                        PatientTransfer::create([
                            'patient_id' => $patient->id,
                            'old_provider_id' => null,
                            'created_by' => null,
                            'closed_at' => null,
                            'unassigned_at' => null,
                        ]);

                        $dumpString = $number . ' | Patient: ' . $patient->id . ', ' . $patient->first_name . ' '
                            . $patient->last_name . ';';
                    }

                    dump($dumpString);
                    $number++;
                });
            });
    }
}
