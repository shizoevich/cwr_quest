<?php


namespace App\Components\PatientForm;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;
use Mpdf\MpdfException;

abstract class BasePatientForm
{
    /** @var array */
    private $data;

    /** @var string */
    protected $templateName;
    
    /** @var string */
    protected $date;
    
    /**
     * BasePatientForm constructor.
     *
     * @param array       $request
     * @param Carbon|null $date
     */
    public function __construct(array $request, Carbon $date = null)
    {
        if(!$date) {
            $date = Carbon::today();
        }
        $this->date = $date->format('m/d/Y');
        $this->data = $this->getPdfFields();
        $this->mapRequestData($request);
    }

    /**
     * @return array
     */
    abstract public function getPdfFields();

    /**
     * @return string
     */
    public function getFontFamily()
    {
        return 'Times';
    }

    abstract public function getFontSize();

    /**
     * @param string $documentName
     * @throws MpdfException
     */
    public function fillDocument($documentName)
    {
        $font = $this->getFontFamily();
        $fontSize = $this->getFontSize();
        Storage::disk('temp_pdf')->put($documentName, Storage::disk('document_templates')->get($this->templateName));
        $tempRoot = config("filesystems.disks.temp_pdf.root");
        $pdf = new Mpdf();
        $pdf->SetImportUse();
        $pdf->setSourceFile("$tempRoot/$documentName");
        foreach ($this->data['pages'] as $key => $page) {
            $pdf->AddPage();
            $tplIdx = $pdf->importPage($key + 1);
            $pdf->useTemplate($tplIdx);
            foreach ($page['texts'] as $text) {
                $pdf->setFont(is_null($text['font']) ? $font : $text['font']);
                $pdf->SetFontSize(is_null($text['font_size']) ? $fontSize : $text['font_size']);
                $pdf->Text($text['x'], $text['y'], $text['value']);
            }
            foreach ($page['checkboxes'] as $checkbox) {
                $pdf->SetXY($checkbox['x'], $checkbox['y']);
                $pdf->WriteHTML($checkbox['value']);
            }
            foreach ($page['radiobuttons'] as $radiobutton) {
                $pdf->SetXY($radiobutton['yes']['x'], $radiobutton['yes']['y']);
                $pdf->WriteHTML($radiobutton['yes']['value']);
                $pdf->SetXY($radiobutton['no']['x'], $radiobutton['no']['y']);
                $pdf->WriteHTML($radiobutton['no']['value']);
            }
            foreach ($page['images'] as $image) {
                if (!empty($image['value'])) {
                    $pdf->Image($image['value'], $image['x'], $image['y'], $image['width'], $image['height']);
                    Storage::disk('temp_pdf')->delete($image['file_name']);
                }
            }
        }
        $filledPdf = $pdf->Output($tempRoot . '/signed_' . $documentName, "S");
        Storage::disk('patients_docs')->put($documentName, $filledPdf);
        Storage::disk('temp_pdf')->delete($documentName);
    }

    /**
     * @param array $request
     */
    protected function mapRequestData(array $request)
    {
        $tempRoot = config("filesystems.disks.temp_pdf.root");
        foreach ($this->data['pages'] as &$page) {
            foreach ($page['texts'] as &$text) {
                if ($text['field'] !== 'date' && array_key_exists($text['field'], $request)) {
                    $value = $request[$text['field']];
                    $text['value'] = strlen($value) > $text['max_length'] ?
                        substr($value, 0, $text['max_length']) : $value;
                }
            }
            foreach ($page['checkboxes'] as &$checkbox) {
                if (array_key_exists($checkbox['field'], $request)) {
                    $checkbox['value'] = $this->checkButton('c', $request[$checkbox['field']]);
                }
            }
            foreach ($page['radiobuttons'] as &$radiobutton) {
                if (array_key_exists($radiobutton['field'], $request)) {
                    $values = $this->checkButton('r', $request[$radiobutton['field']]);
                    $radiobutton['yes']['value'] = $values['yes'];
                    $radiobutton['no']['value'] = $values['no'];
                }
            }
            foreach ($page['images'] as &$image) {
                if (array_key_exists($image['field'], $request) && !empty($request[$image['field']])) {
                    list($type, $gotImage) = explode(';', $request[$image['field']]);
                    list(, $gotImage) = explode(',', $gotImage);
                    $gotImage = base64_decode($gotImage);
                    $imageTmpFile = md5(uniqid()) . $image['field'] . '.png';
                    Storage::disk('temp_pdf')->put($imageTmpFile, $gotImage);
                    $imagePath = "$tempRoot/$imageTmpFile";
                    $imageSize = getimagesize($imagePath);
                    $imageWidth = $imageSize[0] * $image['height'] / $imageSize[1];
                    if ($imageWidth <= $image['max_width']) {
                        $image['x'] += ($image['max_width'] - $imageWidth) / 2;
                    }
                    $image['value'] = $imagePath;
                    $image['file_name'] = $imageTmpFile;
                }
            }
        }
    }

    /**
     * @param $btnType
     * @param $val
     * @param int $size
     * @return array|string|string[]
     */
    private function checkButton($btnType, $val, $size = 11)
    {
        $radioCode = '&#9673;';
        $emptyRadioCode = '&#9675;';
//        $checkboxCode = '&#9745;';
//        $emptyCheckboxCode = '&#9744;';
        $checkbox = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24"><path d="M22 2v20h-20v-20h20zm2-2h-24v24h24v-24zm-5.541 8.409l-1.422-1.409-7.021 7.183-3.08-2.937-1.395 1.435 4.5 4.319 8.418-8.591z"/></svg>';
        $emptyCheckbox = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24"><path d="M22 2v20h-20v-20h20zm2-2h-24v24h24v-24z"/></svg>';
        switch ($btnType) {
            case 'r':   //radiobutton
                if ($val === 'Yes' || $val === true || $val === 'true') {
                    $temp1 = $radioCode;
                    $temp2 = $emptyRadioCode;
                } else {
                    $temp1 = $emptyRadioCode;
                    $temp2 = $radioCode;
                }
                return ['yes' => $temp1, 'no' => $temp2];
            case 'c':   //checkbox
                if ($val === 'Yes' || $val === true || $val === 'true') {
                    return $checkbox;
                }

                return $emptyCheckbox;
            default:
                return [];
        }
    }
}