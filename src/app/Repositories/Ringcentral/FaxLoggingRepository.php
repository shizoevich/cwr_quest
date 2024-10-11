<?php

namespace App\Repositories\Ringcentral;

use App\Helpers\Constant\LoggerConst;
use App\Helpers\Logger\LogActivityFax;
use App\Http\Requests\Fax\DownloadRequest;
use App\Http\Requests\Fax\LoggingListRequest;
use App\Models\FaxModel\Fax;
use App\Models\FaxModel\UserFaxLogActivity;
use App\Patient;
use App\PatientStatus;
use App\User;
use DateTime;
use Illuminate\Http\JsonResponse;

class FaxLoggingRepository implements FaxLoggingRepositoryInterface
{
    public function getLoggingListOfFaxes(LoggingListRequest $request)
    {
        $faxId = $request->input('fax_id');
        $logPagination = UserFaxLogActivity::where('fax_id', $faxId)->orderBy('created_at', 'DESC')->paginate()->toArray();

        $logData = [];

        foreach ($logPagination['data'] as $log) {

            if (empty($log['patient_id']) === false) {
                $patientData = Patient::where('id', $log['patient_id'])->first()->toArray();
                $patient = $patientData['first_name'] . " " . $patientData['last_name'];
            } else {
                $patient = null;
            }

            if(isset(User::with(['roles'])->find( $log['user_id'])->roles['1']['role'])){
                $user = 'Secretary';
            }elseif(isset(User::with(['roles'])->find( $log['user_id'])->roles['0']['role'])){
                $user = 'CWR Billing';
            }

            $patient_status =  isset(Patient::whereId($log['patient_id'])->get()->pluck('full_name')['0']) ?
                PatientStatus::where('id',  Patient::whereId($log['patient_id'])->first()->status_id)->first()->status : null;
                
            // map fax logs data for api
            $logData[] = [
                'id' => $log['id'],
                'subject' => $log['subject'],
                'url' => $log['url'],
                'method' => $log['method'],
                'agent' => $log['agent'],
                'patient' => $patient,
                'user' => $user,
                'patient_id' => empty($log['patient_id']) ? null : $log['patient_id'],
                'patient_status' => $patient_status,
                'fax_id' => empty($log['fax_id']) ? null : $log['patient_id'],
                'created_at' => date_format(new DateTime($log['created_at']), "m:d:Y H:i:s"),
                'updated_at' => date_format(new DateTime($log['updated_at']), "m:d:Y H:i:s"),
            ];
        }

        $data = [];
        $data = [
            'meta' => [
                "from" => $logPagination['from'],
                "last_page" => $logPagination['last_page'],
                "next_page_url" => $logPagination['next_page_url'],
                "path" => $logPagination['path'],
                "per_page" => $logPagination['per_page'],
                "prev_page_url" => $logPagination['prev_page_url'],
                "to" => $logPagination['to'],
                "total" => $logPagination['total'],
            ],
            'data' => $logData
        ];

        return  new JsonResponse(
            [
                'message' => 'Show fax logging list',
                'status' => JsonResponse::HTTP_OK,
                'meta' => $data['meta'],
                'data' => $data['data']
            ],
            200
        );
    }

    public function logging(DownloadRequest $request)
    {
       $faxData = Fax::where("id", $request->input('fax_id'))->first();
       $patientId = is_null($faxData) ? null : $faxData->patient_id;
       if(!empty($request->input('download'))){
        return  LogActivityFax::addToLog(LoggerConst::FAX_DOWNLOADED, $request->input('fax_id'));
       }
       if(empty( $request->input('download'))){
        return LogActivityFax::addToLog(LoggerConst::FAX_VIEWING, $request->input('fax_id'));
       }
    }
}
