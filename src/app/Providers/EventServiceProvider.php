<?php

namespace App\Providers;

use App\Events\PatientDocumentStatusChanged;
use App\Listeners\WriteDocumentStatusChangedComment;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\PatientDocumentSent' => [
            'App\Listeners\WriteDocumentSentComment',
        ],
        'App\Events\PatientDocumentDownloaded' => [
            'App\Listeners\WriteDocumentDownloadInfo',
            'App\Listeners\WriteDocumentDownloadComment',
        ],
        'App\Events\PatientDocumentPreview' => [
            'App\Listeners\WriteDocumentPreviewComment',
        ],
        'App\Events\PatientDocumentUpdateStatus' => [
            'App\Listeners\WriteDocumentUpdateComment'
        ],
        'App\Events\PatientDocumentUpload' => [
            'App\Listeners\WriteDocumentUploadInfo',
        ],
        'App\Events\PatientLeadDocumentUpload' => [
            'App\Listeners\WritePatientLeadDocumentUploadInfo',
        ],
        PatientDocumentStatusChanged::class => [
            WriteDocumentStatusChangedComment::class,
        ],
        'App\Events\PatientDocumentUpdate' => [
            'App\Listeners\WriteDocumentUpdateCommentListener',
        ],
        \App\Events\NeedsWriteSystemComment::class => [
            \App\Listeners\NeedsWriteSystemComment::class,
        ],
        \App\Events\NeedsWriteSystemCommentForPatientInquiry::class => [
            \App\Listeners\NeedsWriteSystemCommentForPatientInquiry::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
