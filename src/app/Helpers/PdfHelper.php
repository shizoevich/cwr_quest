<?php

namespace App\Helpers;

class PdfHelper extends \TCPDF
{
//    private $startTime;
//    private $endTime;
    private $patientString;

    function __construct(
        $orientation = 'P',
        $unit = 'mm',
        $format = 'A4',
        $unicode = true,
        $encoding = 'UTF-8',
        $diskcache = false,
        $pdfa = false,
        $patientString = ""
//        $start = "00:00:00",
//        $end = "00:00:00"
    ) {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
        $this->SetMargins(2.5,4, 1.5);
//        $this->startTime = $start;
//        $this->endTime = $end;
        $this->patientString = $patientString;
    }

    function Header()
    {
        $this->SetFont('calibri_b', 'B', 12);
        $this->SetXY(4.67, $this->h - 25.32);
//        $this->Cell(7.62,0.37,'Concept Healthcare Psychology Group, Inc. ',0,0,'L');
        $this->SetFont('symbol', '', 12);
        $this->SetXY(12.41, $this->h - 25.40);
//        $this->Cell(0.22,0.08,'~',0,0,'L');
        $this->SetFont('calibri_b', 'B', 12);
        $this->SetXY(12.65, $this->h - 25.32);
//        $this->Cell(6.07,0.37,' CoHealth Psychology Services, PA',0,0,'L');
        $this->Image(public_path("/images/pdf-header.png"), 0, 0, 21.48);

        $html = <<<EOD
<table>
 <tr>
  <td>
   Encino Office:<br>
   17777 Ventura Blvd. Suite 103<br>
   Encino, CA. 91316
  </td>  
 </tr>
</table>
EOD;

//        $this->SetXY($this->w - 4, 0.6);
//        $this->SetFont('calibri', '', 8);
//        $this->SetMargins(2.5, 4, 0.5);
//        $this->writeHTML($html, false, 0, true, 1);
        $this->SetMargins(2.5, 5, 1.5);

        $this->SetFont('calibri', '', 11);
        $this->SetXY(2.5, $this->h - 24);
        $this->Cell($this->w - 4,0.37,$this->patientString,0,0,'C');


    }

    function Footer()
    {
        /*$this->SetFont('calibri', '', 8);
        $this->SetXY(2.5, $this->h - 1.5);
        $html = <<<EOD
<table border="0" width="40%">
 <tr>
  <td style="width: 50%; text-align: right">
   Session start time:
  </td>
  <td style="width: 50%; text-align: left">
   {$this->startTime}
  </td>
 </tr>
 <tr>
  <td style="width: 50%; text-align: right">
   end time:
  </td>
  <td style="width: 50%; text-align: left">
   {$this->endTime}
  </td>
 </tr>
</table>
EOD;
        $this->writeHTML($html, false, 0, true, 1);*/

    }
}