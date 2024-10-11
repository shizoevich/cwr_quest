<?php

namespace App\Jobs\Patients\AssessmentForms;

use App\Models\Patient\PatientElectronicDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class Generate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var PatientElectronicDocument
     */
    private $document;
    private $configPath;
    private $jarPath;
    private $mode;
    /**
     * @var
     */
    private $password;

    /**
     * Create a new job instance.
     *
     * @param PatientElectronicDocument $document
     * @param $password
     */
    public function __construct(PatientElectronicDocument $document, $password = null)
    {
        $this->document = $document;
        $this->configPath = config('assessment_form_generator.config_path');
        $this->jarPath = config('assessment_form_generator.jar_path');
        $this->mode = config('assessment_form_generator.mode');
        $this->password = $password;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $command = "java -jar {$this->jarPath}";
        $this->addOptions($command, [
            'data_id'     => $this->document->id,
            'config_path' => $this->configPath,
            's3_upload'   => $this->mode === 'production' ? 'true' : 'false',
        ]);
        if (!is_null($this->password)) {
            $this->addOption($command, 'password', $this->password);
        }
        exec($command . " >> " . storage_path('logs/assessment-generator.log'));
    }

    /**
     * @param $command
     * @param $key
     * @param $value
     *
     * @return string
     */
    private function addOption(&$command, $key, $value)
    {
        if (!is_null($key) && !is_null($value)) {
            $command .= " $key=$value";
        }

        return $command;
    }

    /**
     * @param $command
     * @param array $data
     *
     * @return mixed
     */
    private function addOptions(&$command, array $data)
    {
        foreach ($data as $key => $item) {
            $this->addOption($command, $key, $item);
        }

        return $command;
    }
}
