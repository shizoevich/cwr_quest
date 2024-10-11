<?php

namespace App\Helpers;

use App\Jobs\Officeally\Retry\RetryAddPaymentToAppointment;
use App\Jobs\Officeally\Retry\RetryDeleteAppointment;
use App\Jobs\Officeally\Retry\RetryEditAppointment;
use App\Jobs\Officeally\Retry\RetryUpdatePatient;
use Illuminate\Support\Facades\DB;

class RetryJobQueueHelper
{
    static function dispatchRetryUpdatePatient(string $officeAllyAccount, array $data, int $patientId)
    {
        $updatePatientJobName = class_basename(RetryUpdatePatient::class);

        $job = self::findJobInQueue($updatePatientJobName, ['patientId' => $patientId]);

        if (!$job) {
            return \Bus::dispatchNow(new RetryUpdatePatient($officeAllyAccount, $data, $patientId));
        }

        $mergedData = self::getMergedDataWithExistingJob($job, $data);
            
        DB::table('jobs')->where('payload', $job->payload)->delete();

        \Bus::dispatchNow(new RetryUpdatePatient($officeAllyAccount, $mergedData, $patientId));
    }

    static function dispatchRetryEditAppointment(string $officeAllyAccount, array $data, int $appointmentId)
    {
        $updatePatientJobName = class_basename(RetryEditAppointment::class);

        $job = self::findJobInQueue($updatePatientJobName, ['appointmentId' => $appointmentId]);

        if (!$job) {
            return \Bus::dispatchNow(new RetryEditAppointment($officeAllyAccount, $data, $appointmentId));
        }

        $mergedData = self::getMergedDataWithExistingJob($job, $data);
            
        DB::table('jobs')->where('payload', $job->payload)->delete();

        \Bus::dispatchNow(new RetryEditAppointment($officeAllyAccount, $mergedData, $appointmentId));
    }


    static function checkAppointmentJobs(int $appointmentId)
    {
        $jobInQueue = false;

        DB::table('jobs')
            ->where(function ($query) {
                $deleteAppointmentJobName = class_basename(RetryDeleteAppointment::class);
                $editAppointmentJobName = class_basename(RetryEditAppointment::class);
                $addPaymentJobName = class_basename(RetryAddPaymentToAppointment::class);
                $query->where('payload', 'like', '%' . $deleteAppointmentJobName . '%')
                    ->orWhere('payload', 'like', '%' . $editAppointmentJobName . '%')
                    ->orWhere('payload', 'like', '%' . $addPaymentJobName . '%');
            })
            ->orderBy('id')
            ->each(function ($job) use (&$jobInQueue, $appointmentId) {
                $data = json_decode($job->payload, true);
                $command = unserialize($data['data']['command']);
                $jobAppointmentId = $command->appointmentId;

                if ($jobAppointmentId === $appointmentId) {
                    $jobInQueue = true;
                    return false;
                }
            });

        return $jobInQueue;
    }

    static function checkPatientJobs(int $patientId)
    {
        $updatePatientJobName = class_basename(RetryUpdatePatient::class);
        $jobInQueue = false;

        DB::table('jobs')
            ->where('payload', 'like', '%' . $updatePatientJobName . '%')
            ->orderBy('id')
            ->each(function ($job) use (&$jobInQueue, $patientId) {
                $data = json_decode($job->payload, true);
                $command = unserialize($data['data']['command']);
                $jobPatientId = $command->patientId;

                if ($jobPatientId === $patientId) {
                    $jobInQueue = true;
                    return false;
                }
            });

        return $jobInQueue;
    }

    private static function getMergedDataWithExistingJob($job, $data)
    {
        $payload = json_decode($job->payload, true);
        $command = unserialize($payload['data']['command']);
        $oldData = $command->data;

        return array_merge($oldData, $data);
    }

    private static function findJobInQueue(string $className, array $options)
    {
        $queueJob = null;

        DB::table('jobs')
            ->where('payload', 'like', '%' . $className . '%')
            ->orderBy('id')
            ->each(function ($job) use (&$queueJob, &$options) {
                $data = json_decode($job->payload, true);
                $command = unserialize($data['data']['command']);

                $matchFound = true;
                foreach ($options as $key => $value) {
                    if (!data_get($command, $key) || data_get($command, $key) !== $value) {
                        $matchFound = false;
                        break;
                    }
                }

                if ($matchFound) {
                    $queueJob = $job;
                    return false;
                }
            });

        return $queueJob;
    }
}
