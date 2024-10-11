<?php

namespace App\Http\Controllers;

use App\Repositories\UpdateNotificationSubstitution\UpdateNotificationSubstitutionRepositoryInterface;
use App\Models\UpdateNotificationSubstitution;

class UpdateNotificationSubstitutionController extends Controller
{
    protected $notificationSubstitutionRepository;

    public function __construct(UpdateNotificationSubstitutionRepositoryInterface $notificationSubstitutionRepository)
    {
        $this->notificationSubstitutionRepository = $notificationSubstitutionRepository;
    }

    public function indexApi()
    {
        $substitutions = $this->notificationSubstitutionRepository->all();
        
        return response()->json($substitutions);
    }
}
