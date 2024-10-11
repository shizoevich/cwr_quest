<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\MenuApiForReactLayouts\MenuApiRepositoryInterface;
use Illuminate\Http\JsonResponse;

class MenuApiController extends Controller
{
    /**
     * @var MenuApiRepositoryInterface
     */
    protected $menuApiRepository;

    /**
     * MenuApiController constructor.
     * @param MenuApiRepositoryInterface $menuApiRepository
     */
    public function __construct(MenuApiRepositoryInterface $menuApiRepository)
    {
        $this->menuApiRepository = $menuApiRepository;
    }

    public function index(): JsonResponse
    {
      return response()->json($this->menuApiRepository->getMenuData());
    }
}
