<?php

namespace App\Repositories\Provider\Comments;

use App\Models\ProviderComment;
use App\Provider;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

interface ProviderCommentRepositoryInterface
{
    public function getComments(Provider $provider, array $data = []): LengthAwarePaginator;

    public function create(array $data, int $providerId): ProviderComment;

    public function previewFile(string $fileName);

    public function downloadFile(string $fileName);
}
