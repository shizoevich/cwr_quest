<?php
/**
 * Created by PhpStorm.
 * User: braginec_dv
 * Date: 30.09.2017
 * Time: 16:34
 */

namespace App\Http\Controllers\Utils;

use App\Jobs\GenerateUserSignature;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Helpers\PdfHelper;
use App\UserMeta;

trait PdfUtils
{
    /**
     * function for mapping request's fields to corresponding pdf fields
     * @param array $data - request's fields
     * @return array - array of mapped data
     */
    protected function mapToPdfFields(array $data)
    {
        $result = array();
        $textFields = array(
            "date_of_birth",
            "finalized_at",
            "date_of_service",
            "facility_name",
            "provider_name",
            "provider_license_no",
            "procedure_code",
            "diagnosis_icd_code",
            "long_range_treatment_goal",
            "shortterm_behavioral_objective",
            "units",
            "session_time",
            "status_edit_1_text",
            "status_edit_2_text",
            "status_edit_3_text",
            "status_edit_4_text",
            "status_edit_5_text",
            "additional_comments",
            "interventions",
            "progress_and_outcome",
            "treatment_modality",
            "plan",
            "start_time",
            "end_time",
            "disorientation_status"
        );
        $buttonFields = array(
            "pty_30",
            "pty_45",
            "pty_60",
            "family",
            "group",
            "health_behaviour",
            "depression",
            "withdrawal",
            "disturbed_sleep",
            "disturbed_eating",
            "tearfulness",
            "hopelessness",
            "flat_affect",
            "anxiety",
            "panic_prone",
            "worrisome_thinking",
            "phobic_avoidance",
            "agitated",
            "restless_tension",
            "fearfulness",
            "verbally_abusive",
            "physically_abusive",
            "irritable",
            "anger_outbursts",
            "disruptive_vocalizing",
            "interpersonal_conflict",
            "emotionally_labile",
            "impaired_reality",
            "delusions",
            "hallucinations_vis",
            "hallucinations_aud",
            "danger_to_self",
            "danger_to_others",
            "disordered_thinking",
            "disorientation",
            "limited_self_expression",
            "limited_memory",
            "limited_concentration",
            "limited_judgment",
            "limited_attention",
            "limited_info_processing",
            "status_edit_1",
            "status_edit_2",
            "status_edit_3",
            "status_edit_4",
            "status_edit_5",
            'other_status',
        );

        foreach ($data as $key => $value) {
            if (in_array($key, $textFields)) {
                $result["$key"] = htmlspecialchars($value);
            }
            if (in_array($key, $buttonFields)) {
                $result["$key"] = ($value === "NULL" || $value == false) ? "Off" : "Yes";
            }
        }

        $result["status_edit_1_text"] = (isset($data["status_edit_1"])) ? $data["status_edit_1"] : "";
        $result["status_edit_2_text"] = (isset($data["status_edit_2"])) ? $data["status_edit_2"] : "";
        $result["status_edit_3_text"] = (isset($data["status_edit_3"])) ? $data["status_edit_3"] : "";
        $result["status_edit_4_text"] = (isset($data["status_edit_4"])) ? $data["status_edit_4"] : "";
        $result["status_edit_5_text"] = (isset($data["status_edit_5"])) ? $data["status_edit_5"] : "";
        $result["patient_name"] = (isset($data["first_name"]) && isset($data["last_name"])) ? $data["first_name"] . " " . $data["last_name"] : "";

        return $result;
    }


    /**
     * @param $title
     * @param $value
     * @return string
     */
    protected function simplePdfTableRow($title, $value)
    {
        $html = <<<EOD
<tr>
 <td style="width: 25%; height: 0.8cm; 
 border-top: 0.03cm solid black;
 border-left: 0.03cm solid black;
 border-bottom: 0.03cm solid black;
 ">{$title}</td>
 <td style="width: 75%; height: 0.8cm; 
 border-top: 0.03cm solid black;
 border-right: 0.03cm solid black;
 border-bottom: 0.03cm solid black;
 ">{$value}</td>  
</tr>
EOD;
        return $html;
    }

    protected function patientTableRow($data)
    {
        $patientName = $data['patient_name'] ?? '';
        $dateOfBirth = $data['date_of_birth'] ?? '';
        $html = <<<EOD
<tr>
 <td style="width: 25%; height: 0.8cm; 
 border-top: 0.03cm solid black;
 border-left: 0.03cm solid black;
 border-bottom: 0.03cm solid black;
 ">Patient Name:</td>
 <td style="width: 25%; height: 0.8cm; 
 border-top: 0.03cm solid black;
 border-right: 0.03cm solid black;
 border-bottom: 0.03cm solid black;
 ">{$patientName}</td>
 <td style="width: 25%; height: 0.8cm; 
 border-top: 0.03cm solid black;
 border-bottom: 0.03cm solid black;
 ">Date of Birth:</td>
 <td style="width: 25%; height: 0.8cm; 
 border-top: 0.03cm solid black;
 border-right: 0.03cm solid black;
 border-bottom: 0.03cm solid black;
 ">{$dateOfBirth}</td>  
</tr>
EOD;

        return $html;
    }

    protected function dateTableRow($data)
    {
        $dateOfService = $data['date_of_service'] ?? '';
        $dateOfDocumentation = $data['finalized_at'] ?? '';
        $html = <<<EOD
<tr>
 <td style="width: 25%; height: 0.8cm; 
 border-top: 0.03cm solid black;
 border-left: 0.03cm solid black;
 border-bottom: 0.03cm solid black;
 ">Date Of Service:</td>
 <td style="width: 25%; height: 0.8cm; 
 border-top: 0.03cm solid black;
 border-right: 0.03cm solid black;
 border-bottom: 0.03cm solid black;
 ">{$dateOfService}</td>
 <td style="width: 25%; height: 0.8cm; 
 border-top: 0.03cm solid black;
 border-bottom: 0.03cm solid black;
 ">Date of Documentation:</td>
 <td style="width: 25%; height: 0.8cm; 
 border-top: 0.03cm solid black;
 border-right: 0.03cm solid black;
 border-bottom: 0.03cm solid black;
 ">{$dateOfDocumentation}</td>  
</tr>
EOD;

        return $html;
    }

    /**
     * @param $title
     * @param $value
     * @return string
     */
    protected function pdfCheckBox($title, $value)
    {
        $html = <<<EOD
<td style="width: 20%">
     <input type="checkbox" name="{$title}" value="{$value}" checked="{$value}" readonly="readonly"> {$title}
    </td>
EOD;
        return $html;
    }

    protected function generatePdfNoteOnFly($data)
    {
        set_time_limit(300);

        $data['date_of_service'] = $this->formatDate($data['date_of_service'] ?? null);
        $data['date_of_birth'] = $this->formatDate($data['date_of_birth'] ?? null);
        $data['finalized_at'] = $this->formatDate($data['finalized_at'] ?? null);

        $noteId = $data['id'];
        $creatorId = $data['provider_id'];
        $data = $this->mapToPdfStream($data);

        $nonOrderedTextFields = array(
            "start_time",
            "end_time",
            "provider_name",
            "provider_license_no",
            "diagnosis_icd_code",
            "long_range_treatment_goal",
            "shortterm_behavioral_objective",
            "treatment_modality"
        );

        $orderedTextFields = array(
            "interventions",
            "progress_and_outcome",
            "plan"
        );

        $currentStatusCheckBoxes = array(
            "depression",
            "anxiety",
            "anger_outbursts",
            "impaired_reality",
            "disorientation",
            "withdrawal",
            "panic_prone",
            "verbally_abusive",
            "delusions",
            "limited_self_expression",
            "disturbed_sleep",
            "worrisome_thinking",
            "physically_abusive",
            "hallucinations_vis",
            "limited_memory",
            "disturbed_eating",
            "phobic_avoidance",
            "irritable",
            "hallucinations_aud",
            "limited_concentration",
            "tearfulness",
            "agitated",
            "disruptive_vocalizing",
            "danger_to_self",
            "limited_judgment",
            "hopelessness",
            "restless_tension",
            "interpersonal_conflict",
            "danger_to_others",
            "limited_attention",
            "flat_affect",
            "fearfulness",
            "emotionally_labile",
            "disordered_thinking",
            "limited_info_processing",
        );

        if (!file_exists(base_path('vendor/tecnickcom/tcpdf/fonts/calibri.php'))) {
            \TCPDF_FONTS::addTTFfont(public_path("/fonts/calibri.ttf"));
        }
        if (!file_exists(base_path('vendor/tecnickcom/tcpdf/fonts/calibri_b.php'))) {
            \TCPDF_FONTS::addTTFfont(public_path("/fonts/calibri_bold.ttf"));
        }

        $patientString = (isset($data["patient_name"])) ? "Patient Name: " . $data["patient_name"] : "";

        $letterH = 27.94;
//        $pdf = new PdfHelper('P', 'cm', 'LETTER', true, 'UTF-8', false, false, $data["start_time"], $data["end_time"]);
        $pdf = new PdfHelper('P', 'cm', 'LETTER', true, 'UTF-8', false, false, $patientString);
        $pdf->AddPage();

        $pdf->setFont('Calibri_b', 'B', 14.05);
        $pdf->SetXY(2.5, $letterH - 24.26);
        $pdf->SetTextColor(54, 95, 145);
        $pdf->Cell($pdf->getPageWidth() - 4, 2.33, "PROGRESS NOTE", 0, 0, 'C');

        $pdf->Ln(1.04);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->setFont('Calibri', '', 8.04);


//        $html = "<table border='0' cellpadding=\"20\">";
        $html = <<<EOD
<table border="0" cellpadding="4">
EOD;

        $html = $html . $this->patientTableRow($data);
        $html = $html . $this->dateTableRow($data);

//        add non ordered text fields to pdf
        foreach ($nonOrderedTextFields as $value) {
            if (!isset($data[$value])) {
                $data[$value] = "";
            }
            if ($value == "start_time") {
                $html = $html . <<<EOD
<tr>
 <td style="width: 7%; height: 0.4cm; 
 border-left: 0.03cm solid black;
 border-top: 0.03cm solid black;
 ">Session</td>
 <td style="width: 18%; height: 0.4cm; 
 border-top: 0.03cm solid black;
 ">start time:</td>
 <td style="width: 75%; height: 0.4cm;  
 border-right: 0.03cm solid black;
 border-top: 0.03cm solid black;
 ">{$data[$value]}</td>  
</tr>
EOD;
            } elseif ($value == "end_time") {
                $html = $html . <<<EOD
<tr>
 <td style="width: 7%; height: 0.4cm; 
 border-bottom: 0.03cm solid black;
 border-left: 0.03cm solid black;
 "></td>
 <td style="width: 18%; height: 0.4cm; 
 border-bottom: 0.03cm solid black;
 ">end time:</td>
 <td style="width: 75%; height: 0.4cm; 
 border-bottom: 0.03cm solid black;
 border-right: 0.03cm solid black;
 ">{$data[$value]}</td>  
</tr>
EOD;

            } else {
                $sanitizeTitle = implode(" ", array_map('ucfirst', explode('_', $value))) . ":";
                $html = $html . $this->simplePdfTableRow($sanitizeTitle, $data[$value]);
            }
        }

//        add Current Status fields block to pdf

        $currentStatus = <<<EOD
<tr>
 <td style="width: 100%; border-top: 0.03cm solid black; border-left: 0.03cm solid black; border-right: 0.03cm solid black" >
  1. Current Status: <br><br>
  <table border="1" width="96%">
EOD;
        for ($i = 0; $i < count($currentStatusCheckBoxes); $i += 5) {
            $currentStatus = $currentStatus . "<tr>";
            for ($j = 0; $j < 5; $j++) {
                $sanitizeTitle = implode(" ", array_map('ucfirst', explode('_', $currentStatusCheckBoxes[$i + $j])));
                $value = (isset($data[$currentStatusCheckBoxes[$i + $j]])) ? $data[$currentStatusCheckBoxes[$i + $j]] : "false";
                if ($currentStatusCheckBoxes[$i + $j] == "disorientation") {
                    $ds = (isset($data["disorientation_status"])) ? $data["disorientation_status"] : "";
                    $currentStatus = $currentStatus . <<<EOD
<td style="width: 20%">
     <input type="checkbox" name="{$sanitizeTitle}" value="{$value}" checked="{$value}" readonly="readonly"> {$sanitizeTitle} {$ds}
 <!--<input type="radio" name="disorientation_status" value="T"> T-->
 <!--<input type="radio" name="disorientation_status" value="PL"> PL-->
 <!--<input type="radio" name="disorientation_status" value="P"> P-->
</td>
EOD;
                } else {
                    $currentStatus = $currentStatus . $this->pdfCheckBox($sanitizeTitle, $value);
                }
            }
            $currentStatus = $currentStatus . "</tr>";
        }
        $value = isset($data['other_status']) ? $data['other_status'] : 'false';
        $currentStatus .= <<<EOD
        <tr>
            <td></td><td></td><td></td><td></td>
            <td style="width: 20%">
                <input type="checkbox" name="other" value="Other" checked="{$value}" readonly="readonly"> Other
            </td>
        </tr>
        
EOD;

        if (!isset($data['additional_comments'])) {
            $data['additional_comments'] = "";
        }
        $currentStatus = $currentStatus . <<<EOD
  </table>
 </td>
</tr>
<tr>
 <td style="width: 25%; height: 2cm;  
 border-left: 0.03cm solid black;
 border-bottom: 0.03cm solid black;
 ">Additional comments:</td>
 <td style="width: 75%; height: 2cm; 
 border-right: 0.03cm solid black;
 border-bottom: 0.03cm solid black;
 ">{$data['additional_comments']}</td>  
</tr>
EOD;
        $html = $html . $currentStatus;
//        Current Status fields block ended

//        Add ordered tex fields to pdf
        foreach ($orderedTextFields as $key => $value) {
            $sanitizeTitle = (string)($key + 2) . ". " . implode(" ", array_map('ucfirst', explode('_', $value))) . ":";
            if (!isset($data[$value])) {
                $data[$value] = "";
            }
            $html = $html . $this->simplePdfTableRow($sanitizeTitle, $data[$value]);
        }

        $html = $html . "</table>";
        $pdf->SetTopMargin(5.73);
        $pdf->writeHTML($html, true, 0, true, 1);
        $pdf->Ln(1.5);
        $pdf->SetFont('calibri_b', 'B', 11.04);
        $html = <<<EOD
<table style="width: 100%">
 <tr>
  <td style="width: 15%; height: 1cm; vertical-align: top">
   Signature:
  </td>
  <td style="width: 85%; height: 1cm; vertical-align: bottom">
    ________________________________________________
  </td>
 </tr>
</table>
EOD;
        if ($pdf->GetY() > 25) {
            $pdf->SetAutoPageBreak(false, 1);
            $delta = 1.53;
        } else {
            $pdf->SetAutoPageBreak(true, 1);
            $delta = 2;
        }

        $pdf->writeHTML($html, false, 0, true, 1);

//        $providerSignPng = UserMeta::where('user_id', $userId)->firstOrFail()->signature;
        $user = User::withTrashed()->where('provider_id', $creatorId)->firstOrFail();
        $providerSignPng = $user->meta()->withTrashed()->first()->signature;
        if(empty($providerSignPng) || !Storage::disk('signatures')->exists($providerSignPng)) {
            \Bus::dispatchNow(new GenerateUserSignature($user->id));
//            $user->refresh();
            $providerSignPng = $user->meta()->withTrashed()->first()->signature;
        }
        Storage::disk('temp_pdf')->put($providerSignPng, Storage::disk('signatures')->get($providerSignPng));


        $fullPngSignPath = config("filesystems.disks.temp_pdf.root") . "/" . $providerSignPng;
//        $fullResizePngSignPath = config("filesystems.disks.temp_pdf.root") . "/resize_" . $providerSignPng;
//        $cmd = "convert -resize 120x68 -filter Gaussian -density 50x50   ";
//        $cmd = $cmd . $fullPngSignPath . " " . $fullResizePngSignPath;
//        exec($cmd);

        $im = imagecreatefrompng($fullPngSignPath);
        imagealphablending($im, false);
        imagesavealpha($im, true);
        for ($x = 0; $x < imagesx($im); $x++) {
            for ($y = 0; $y < imagesy($im); $y++) {
                $rgba = imagecolorsforindex($im, imagecolorat($im, $x, $y));
                if ($rgba['alpha'] != 127) {
                    $pixelColor = imagecolorallocatealpha($im, 0, 0, 0, 0);
                    imagesetpixel($im, $x, $y, $pixelColor);
                } else {
                    $pixelColor = imagecolorallocatealpha($im, 0, 0, 0, 127);
                    imagesetpixel($im, $x, $y, $pixelColor);
                }
            }
        }
        imagepng($im, config("filesystems.disks.temp_pdf.root") . "/" . $providerSignPng);

        $imageSize = getimagesize($fullPngSignPath);
//        $imageScale = $imageSize[0] / 120;
//        $pdf->SetXY(8, $pdf->GetY() - $delta);
//        $pdf->setImageScale($imageScale);
        $pdf->Image($fullPngSignPath, 8, $pdf->GetY()- 2.4, '', 1.8);


        $tempPdfLoc = config("filesystems.disks.temp_pdf.root") . "/" . $noteId . ".pdf";
        $pdf->Output($tempPdfLoc, "F");
        Storage::disk('progress_notes')->put($noteId . ".pdf", File::get($tempPdfLoc));
        Storage::disk('temp_pdf')->delete($noteId . ".pdf");
        Storage::disk('temp_pdf')->delete($providerSignPng);
    }

    protected function formatDate($dateStr)
    {
        return is_null($dateStr) ? null : Carbon::parse($dateStr)->format('m/d/Y');
    }

    /**
     * @param array $data
     * @return array
     */
    protected function mapToPdfStream(array $data)
    {
        $result = $this->mapToPdfFields($data);
        $buttonFields = array(
            "pty_30",
            "pty_45",
            "pty_60",
            "family",
            "group",
            "health_behaviour",
            "depression",
            "withdrawal",
            "disturbed_sleep",
            "disturbed_eating",
            "tearfulness",
            "hopelessness",
            "flat_affect",
            "anxiety",
            "panic_prone",
            "worrisome_thinking",
            "phobic_avoidance",
            "agitated",
            "restless_tension",
            "fearfulness",
            "verbally_abusive",
            "physically_abusive",
            "irritable",
            "anger_outbursts",
            "disruptive_vocalizing",
            "interpersonal_conflict",
            "emotionally_labile",
            "impaired_reality",
            "delusions",
            "hallucinations_vis",
            "hallucinations_aud",
            "danger_to_self",
            "danger_to_others",
            "disordered_thinking",
            "disorientation",
            "limited_self_expression",
            "limited_memory",
            "limited_concentration",
            "limited_judgment",
            "limited_attention",
            "limited_info_processing",
            "status_edit_1",
            "status_edit_2",
            "status_edit_3",
            "status_edit_4",
            "status_edit_5",
            'other_status',
        );
        foreach ($result as $key => $value) {
            if (in_array($key, $buttonFields)) {
                $result["$key"] = ($value === "Yes") ? "checked" : "false";
            }
        }
        return $result;
    }
}