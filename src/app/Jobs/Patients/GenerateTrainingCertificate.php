<?php

namespace App\Jobs\Patients;

use App\Training;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;

class GenerateTrainingCertificate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $training_id;
    private $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($training_id, $user)
    {
        $this->training_id = $training_id;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $newFileName = md5(uniqid(time())) . '.pdf';
        Storage::disk('temp_pdf')->put($newFileName, Storage::disk('document_templates')->get('staff_certificate.pdf'));

        $temp_root = config("filesystems.disks.temp_pdf.root");

        $pageWidth = 279.4;
        $pageHeight = 215.9;

        $pdf = new Mpdf(['format' => [$pageWidth, $pageHeight]]);
        $pdf->SetImportUse();
        $pdf->setSourceFile("$temp_root/$newFileName");
        $pdf->AddPage();
        $tplIdx = $pdf->importPage(1);
        $pdf->useTemplate($tplIdx);
        $pdf->setFont('Arial');

        $pdf->SetFontSize(24);
        $newX = $pageWidth / 2 - strlen($this->user->name) * 2;
        $pdf->Text($newX, 91, $this->user->name);

        $pdf->SetFontSize(16);
        $pdf->Text(104, 114, 'Change Within Reach, Inc.');

        $examDate = Training::findOrFail($this->training_id);

        $pdf->SetFontSize(14);
        $pdf->Text(67, 175.5, $examDate->start_date->format('m.d.Y'));

        $filledPdf = $pdf->Output('Certificate.pdf', "S");
        Storage::disk('temp_pdf')->delete($newFileName);

        return $filledPdf;
    }
}
