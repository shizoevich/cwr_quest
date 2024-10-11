<?php

namespace App\Console\Commands;

use App\Models\SubmittedReauthorizationRequestForm;
use Illuminate\Console\Command;

class SyncRelatedColumnsInReauthorizationForms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:reauthorization-forms-related-columns';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the patient_id column in the submitted_reauthorization_request_forms table';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        SubmittedReauthorizationRequestForm::query()
            ->where(function ($query) {
                $query->whereNull('patient_id')
                    ->orWhereNull('submitted_by');
            })
            ->each(function ($form) {
                $document = $form->document;

                if (!isset($document)) {
                    return;
                }

                $submittedBy = SubmittedReauthorizationRequestForm::getSubmittedBy($document);

                $form->update([
                    'patient_id' => $document->patient_id,
                    'submitted_by' => $submittedBy,
                ]);
            });
    }
}
