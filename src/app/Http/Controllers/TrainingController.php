<?php

namespace App\Http\Controllers;

use App\Training;
use App\Models\VideoTraining;
use App\Http\Requests\Training\DownloadCertificate as DownloadCertificateRequest;
use App\Http\Requests\Training\UploadHarassmentCetificate as UploadHarassmentCetificateRequest;
use App\Jobs\Patients\GenerateTrainingCertificate;
use App\UserMeta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TrainingController extends Controller
{
    /**
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = \Auth::user();

        $attempts = null;
        $lastTrainings = Training::query()
            ->where('score', '>', 10)
            ->where('user_id', '=', $user->id)
            ->latest()
            ->limit(2)
            ->get();

        if ($lastTrainings->count() > 0) {
            $attempts = Training::query()
                ->where('user_id', '=', $user->id)
                ->where('created_at', '<', $lastTrainings->first()->created_at)
                ->when($lastTrainings->count() > 1, function ($query) use ($lastTrainings) {
                    /** @var \Illuminate\Database\Eloquent\Builder $query */
                    $query->where('created_at', '>', $lastTrainings->last()->created_at);
                })
                ->where(function($query) {
                    $query->whereNull('score')
                        ->orWhere('score', '<', 11);
                })
                ->count() + 1;
        }

        $videoTrainings = $user->isAdmin() ? collect() : VideoTraining::query()->orderBy('index')->get();

        return view("training.index", [
            'lastTraining' => $lastTrainings->first(),
            'attempts' => $attempts,
            'videoTrainings' => $videoTrainings,
        ]);
    }

    public function downloadCertificate(DownloadCertificateRequest $request) {
        $user = \Auth::user();
        $certificate = \Bus::dispatchNow(new GenerateTrainingCertificate($request->training_id, $user));
        $documentName = $user->name . ' Certificate.pdf';
        $mime = Storage::disk('document_templates')->mimeType('staff_certificate.pdf');

        return response($certificate, 200, [
            "Content-Type" => $mime,
            "Content-disposition" => "attachment; filename=\"" . $documentName . "\"",
        ]);
    }

    public function uploadHarassmentCertificate(UploadHarassmentCetificateRequest $request)
    {
        $user = \Auth::user();
        $file = $request->file('harassment_certificate');
        $ext = $file->getClientOriginalExtension();
        $fileName = md5(uniqid(time())) . '.' . $ext;
        $userMeta = UserMeta::where('user_id', $user->id)->firstOrFail();

        Storage::disk('harassment_certificates')->put($fileName, file_get_contents($file));
        if (!empty($userMeta->harassment_certificate_aws_name)) {
            Storage::disk('harassment_certificates')->delete($userMeta->harassment_certificate_aws_name);
        }
        $userMeta->harassment_certificate_original_name = $file->getClientOriginalName();
        $userMeta->harassment_certificate_aws_name = $fileName;
        $userMeta->save();

        return response()->json([
            'message' => 'Harassment Certificate successfully uploaded.'
        ]);
    }

    public function getHarassmentCertificateName()
    {
        $user = \Auth::user();       
        $userMeta = UserMeta::where('user_id', $user->id)->firstOrFail();
        if (!$userMeta) {
            return response()->json(['error' => 'User meta not found'], 404);
        }

        return response()->json([
            'harassment_certificate_original_name' => $userMeta->harassment_certificate_original_name
        ]);
    }
}
