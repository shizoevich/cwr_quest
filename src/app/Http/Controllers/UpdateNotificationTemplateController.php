<?php

namespace App\Http\Controllers;

use App\Repositories\UpdateNotificationTemplate\UpdateNotificationTemplateRepositoryInterface;
use App\Http\Requests\UpdateNotificationTemplate\Store as StoreRequest;
use App\Http\Requests\UpdateNotificationTemplate\Update as UpdateRequest;
use App\Models\UpdateNotificationTemplate;

class UpdateNotificationTemplateController extends Controller
{
    protected $notificationTemplateRepository;

    public function __construct(UpdateNotificationTemplateRepositoryInterface $notificationTemplateRepository)
    {
        $this->notificationTemplateRepository = $notificationTemplateRepository;
    }

    public function index()
    {
        return view('update-notification-templates.index');
    }

    public function indexApi()
    {
        $templates = $this->notificationTemplateRepository->all();
        
        return response()->json($templates);
    }

    public function create()
    {
        return view('update-notification-templates.form');
    }

    public function store(StoreRequest $request)
    {
        $this->notificationTemplateRepository->create($request);

        return redirect()->route('update-notification-templates.index');
    }

    public function show(UpdateNotificationTemplate $template)
    {
        return response()->json([
            'template' => $template,
        ]);
    }

    public function edit(UpdateNotificationTemplate $template)
    {
        $data = [
            'template' => $template,
        ];

        return view('update-notification-templates.form', $data);
    }

    public function update(UpdateRequest $request, UpdateNotificationTemplate $template)
    {
        $this->notificationTemplateRepository->update($request, $template);

        return redirect()->route('update-notification-templates.index');
    }

    public function destroy(UpdateNotificationTemplate $template)
    {
        $this->notificationTemplateRepository->delete($template);

        return response()->json([
            'success' => true
        ]);
    }
}
