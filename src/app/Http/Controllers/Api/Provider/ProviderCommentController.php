<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\Provider\Comments\IndexRequest;
use App\Http\Requests\Provider\Comments\StoreRequest;
use App\Provider;
use App\Repositories\Provider\Comments\ProviderCommentRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProviderCommentController extends Controller
{
    /**
     * @var ProviderCommentRepositoryInterface
     */
    protected $providerCommentRepository;

    /**
     * ProviderCommentController constructor.
     * @param ProviderCommentRepositoryInterface $providerCommentRepository
     */
    public function __construct(ProviderCommentRepositoryInterface $providerCommentRepository)
    {
        $this->providerCommentRepository = $providerCommentRepository;
    }

    public function index(IndexRequest $request, Provider $provider): JsonResponse
    {
        return response()->json($this->providerCommentRepository->getComments($provider, $request->validated()));
    }

    public function store(StoreRequest $request, Provider $provider): JsonResponse
    {
        return response()->json($this->providerCommentRepository->create($request->validated(), $provider->id));
    }

    public function previewFile(string $fileName)
    {
        return $this->providerCommentRepository->previewFile($fileName);
    }

    public function downloadFile(string $fileName)
    {
        return $this->providerCommentRepository->downLoadFile($fileName);
    }
}
