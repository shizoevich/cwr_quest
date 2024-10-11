<?php

namespace App\Repositories\Provider\Comments;

use App\Models\ProviderComment;
use App\Provider;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProviderCommentRepository implements ProviderCommentRepositoryInterface
{
    public function getComments(Provider $provider, array $data = []): LengthAwarePaginator
    {
        $page = isset($data['page']) ? $data['page'] : 1;

        $comments = $provider->profileComments()->orderBy('created_at', 'desc')->paginate(10, ['*'], 'page', $page);
        $comments->load(['admin']);

        return $comments;
    }

    public function create(array $data, int $providerId): ProviderComment
    {
        $originalFileName = null;
        $awsFileName = null;
    
        if (isset($data['file'])) {
            $file = $data['file'];
            $extension = $file->getClientOriginalExtension();
            $awsFileName = md5(uniqid(time())) . '.' . $extension;
            $originalFileName = $file->getClientOriginalName();

            Storage::disk('therapists_comments_files')->put($awsFileName, file_get_contents($file));
        }
        
        $comment = ProviderComment::create([
            'admin_id' => Auth::user()->id,
            'provider_id' => $providerId,
            'comment' => isset($data['comment']) ? $data['comment'] : null,
            'original_file_name' => $originalFileName,
            'aws_file_name' => $awsFileName
        ]);

        $comment->load(['admin']);

        return $comment;
    }

    public function previewFile(string $fileName)
    {
        $file = Storage::disk('therapists_comments_files')->get($fileName);
        $fileMimeType = Storage::disk('therapists_comments_files')->mimeType($fileName);

        $cookie = cookie('document-preview', "true", 0.05, null, null, false, false);
        return response($file, 200, [
            'Content-Type' => $fileMimeType,
            'Content-Disposition' => 'inline; filename="' . $fileName . '"'
        ])->cookie($cookie);
    }

    public function downloadFile(string $fileName)
    {
        $file = Storage::disk('therapists_comments_files')->get($fileName);
        $fileMimeType = Storage::disk('therapists_comments_files')->mimeType($fileName);
        $originalFileName = ProviderComment::where('aws_file_name', $fileName)->first()->original_file_name;

        $cookie = cookie('document-download', "true", 0.05, null, null, false, false);
        return response($file, 200, [
            'Content-Type' => $fileMimeType,
            'Content-Disposition' => "attachment; filename=\"" . $originalFileName . "\"",
        ])->cookie($cookie);
    }
}
