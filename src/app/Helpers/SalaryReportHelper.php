<?php
/**
 * Created by PhpStorm.
 * User: eremenko_aa
 * Date: 17.11.2017
 * Time: 11:24
 */

namespace App\Helpers;


use App\Models\Billing\BillingPeriod;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\Element\Header;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;

class SalaryReportHelper {

    const SM = 569.152; //1sm = ...twip
    
    /** @var array */
    private $mainFontStyle;
    /** @var array */
    private $tableHeaderFontStyle;
    
    
    /** @var array */
    private $mainFontStyleRed;
    
    /** @var array */
    private $totalRowStyles;
    
    /** @var array */
    private $headerRowStyles;
    
    /** @var array */
    private $mainTableStyles;
    
    /** @var array */
    private $cellContentCenter;
    
    
    public function __construct()
    {
        $this->initFontStyles();
        $this->initTableStyles();
    }
    
    private function initTableStyles() {
        $this->totalRowStyles = [
            'bgColor' => 'FCF4DB',
        ];
        $this->headerRowStyles = [
            'bgColor' => 'F4F4F4',
        ];
        $this->mainTableStyles = [
            'borderColor' => 'ffffff',
            'borderSize'  => 1,
            'cellMarginTop'  => 100,
            'cellMarginBottom'  => 100,
        ];
        $this->cellContentCenter = [
            'align' => 'center',
        ];
    }
    
    /**
     * @param int  $size
     * @param bool $bold
     * @param bool $italic
     *
     * @return array
     */
    private function getFontStyle(int $size, bool $bold = false, bool $italic = false)
    {
        return [
            'name' => 'Open Sans',
            'size' => $size,
            'bold' => $bold,
            'italic' => $italic,
        ];
    }
    
    private function initFontStyles()
    {
        $this->mainFontStyle = $this->getFontStyle(7);
        $this->tableHeaderFontStyle = $this->getFontStyle(8, true);
        $this->mainFontStyleRed = array_merge($this->getFontStyle(7, true), ['color' => 'A94442']);
    }
    
    /**
     * @param Section $section
     */
    private function addLogo(Section $section) {
        $header = $section->addHeader(Header::AUTO);
        $header->addImage(public_path("/images/cwr-logo.png"),array(
            'align' => 'left',
            'width' => Converter::cmToPoint(7),
        ));
        /**
         * add empty line
         */
        $header->addText('');
    }
    
    /**
     * @param Section $section
     * @param         $provider
     */
    private function addHead(Section $section, $provider) {
        $table = $section->addTable([
            'borderColor' => 'ffffff',
            'borderSize'  => 1,
            'cellMargin'  => 50,
        ]);
        $table->addRow();
        $cell = $table->addCell(Converter::cmToTwip(9));
        $cell->addText('Statement Date: ' . Carbon::now()->format('m/d/Y'), $this->getFontStyle(8));
        $cell = $table->addCell(Converter::cmToTwip(9.5));
        $cell->addText('Provider: ' . $provider->provider_name, $this->getFontStyle(8), [
            'align' => 'right',
        ]);

        $section->addText('Provider Compensation Statement', $this->getFontStyle(12, true), [
            'align' => 'center',
            'space' => [
                'before' => Converter::cmToTwip(0.5),
            ]
        ]);
    }
    
    /**
     * @param Section $section
     * @param         $provider
     */
    private function addOverviewHead(Section $section, $provider) {
        $section->addText('Overview', $this->getFontStyle(14, true), [
            'align' => 'center',
            'space' => [
                'before' => Converter::cmToTwip(0.3),
                'after' => Converter::cmToTwip(0.2),
            ]
        ]);
    }
    
    /**
     * @param Section $section
     * @param         $filters
     *
     * @return mixed|string
     */
    private function addDates(Section $section, $filters)
    {
        $reportDate = "";
        if(key_exists('selected_filter_type', $filters) && !is_null($filters['selected_filter_type'])) {
            switch($filters['selected_filter_type']) {
                case 1:
                    if(key_exists('date_from', $filters) && !is_null($filters['date_from'])) {
                        $reportDate = $filters['date_from'];
                    }
                    break;
                case 2:
                    if(key_exists('date_from', $filters) && !is_null($filters['date_from'])
                        && key_exists('date_to', $filters) && !is_null($filters['date_to'])) {
                        $reportDate = $filters['date_from'] . ' - ' . $filters['date_to'];
                    }
                    break;
                case 3:
                    if(key_exists('month', $filters) && !is_null($filters['month'])) {
                        $reportDate = $filters['month'];
                    }
                    break;
                case 4:
                case 5:
                    $billingPeriod = BillingPeriod::findOrFail(data_get($filters, 'billing_period_id'));
                    $startDate = Carbon::parse($billingPeriod->start_date)->format('m/d/Y');
                    $endDate = Carbon::parse($billingPeriod->end_date)->format('m/d/Y');
                    $reportDate = $startDate . ' - ' . $endDate;
                    break;
            }
        }
        if(empty($reportDate)){
            $reportDate = Carbon::now()->subMonth()->format('F, Y');
        }
        $section->addText('Current Pay Period: ' . $reportDate, $this->getFontStyle(8), [
            'align' => 'center',
            'space' => [
                'before' => Converter::cmToTwip(0.25),
            ]
        ]);
        
        return $reportDate;
    }
    
    /**
     * @param Section $section
     * @param         $salary
     * @param         $cellWidth
     */
    private function addSalary(Section $section, $salary, $cellWidth)
    {
        $table = $section->addTable($this->mainTableStyles);
        
        $grandTotal = [
            'visits_per_billing_period' => 0,
            'amount_paid' => 0,
        ];
        
        if(!empty($salary['regular']) || !empty($salary['missing_progress_notes'])) {
            $table->addRow();
            $table->addCell($cellWidth['title'], $this->headerRowStyles)->addText('Current Pay Period', $this->tableHeaderFontStyle);
            $table->addCell($cellWidth['visits_count'], $this->headerRowStyles)->addText('# of Visits', $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['amount'], $this->headerRowStyles)->addText('Amount', $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['notes'], $this->headerRowStyles)->addText('Additional Comments', $this->tableHeaderFontStyle);
            if(!empty($salary['regular'])) {
                $table->addRow();
                $table->addCell($cellWidth['title'])->addText('Visits with Completed Progress Notes / Initial Assessments', $this->mainFontStyle);
                $table->addCell($cellWidth['visits_count'])->addText($salary['regular']['visits_per_billing_period'], $this->mainFontStyle, $this->cellContentCenter);
                $table->addCell($cellWidth['amount'])->addText(format_money($salary['regular']['amount_paid']), $this->mainFontStyle, $this->cellContentCenter);
                $table->addCell($cellWidth['notes'])->addText('- paid in full (See details below)', $this->mainFontStyle);
    
                $grandTotal['visits_per_billing_period'] += $salary['regular']['visits_per_billing_period'];
                $grandTotal['amount_paid'] += $salary['regular']['amount_paid'];
            }
            $totalVisitsWithMissingNotes = [
                'visits_per_billing_period' => 0,
                'amount_paid' => 0,
            ];
            if(!empty($salary['missing_progress_notes']) && !empty($salary['missing_progress_notes']['data'])) {
                foreach ($salary['missing_progress_notes']['data'] as $item) {
                    $table->addRow();
                    $table->addCell($cellWidth['title'])->addText('Visits with Missing Progress Notes / Initial Assessments', $this->mainFontStyleRed);
                    $table->addCell($cellWidth['visits_count'])->addText($item['visits_per_billing_period'], $this->mainFontStyleRed, $this->cellContentCenter);
                    $table->addCell($cellWidth['amount'])->addText(format_money($item['amount_paid']), $this->mainFontStyleRed, $this->cellContentCenter);
                    $table->addCell($cellWidth['notes'])->addText('- paid @ ' . format_money($item['fee_per_missing_pn'], '$', 2) . ' per visit', $this->mainFontStyleRed);
                
                    $grandTotal['visits_per_billing_period'] += $item['visits_per_billing_period'];
                    $grandTotal['amount_paid'] += $item['amount_paid'];
    
                    $salary['missing_progress_notes']['visits_per_billing_period'] += $item['visits_per_billing_period'];
                    $salary['missing_progress_notes']['amount_paid'] += $item['amount_paid'];
                }
            }
            $table->addRow();
            $fontStyle = $this->getFontStyle(7, true);
            $table->addCell($cellWidth['title'], $this->totalRowStyles)->addText('Total # of Visits / Hours worked', $fontStyle);
            $table->addCell($cellWidth['visits_count'], $this->totalRowStyles)->addText((int)__data_get($salary, 'regular.visits_per_billing_period', 0) + (int)__data_get($salary, 'missing_progress_notes.visits_per_billing_period', 0), $fontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['amount'], $this->totalRowStyles)->addText(format_money((float)__data_get($salary, 'regular.amount_paid', 0) + (float)__data_get($salary, 'missing_progress_notes.amount_paid', 0)), $fontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['notes'], $this->totalRowStyles)->addText('- included in this payment', $fontStyle);
            $table->addRow();
            $table->addCell()->getStyle()->setGridSpan(4);
        }

        if(!empty($salary['refunds_for_completed_progress_notes'])) {
            $table->addRow();
            $table->addCell($cellWidth['title'], $this->headerRowStyles)->addText('Previous Pay Period', $this->tableHeaderFontStyle);
            $table->addCell($cellWidth['visits_count'], $this->headerRowStyles)->addText('# of Visits', $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['amount'], $this->headerRowStyles)->addText('Amount', $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['notes'], $this->headerRowStyles)->addText('Additional Comments', $this->tableHeaderFontStyle);
            
            $table->addRow();
            $table->addCell($cellWidth['title'], $this->totalRowStyles)->addText('Progress Notes / Initial Assessments Completed', $this->mainFontStyle);
            $table->addCell($cellWidth['visits_count'], $this->totalRowStyles)->addText($salary['refunds_for_completed_progress_notes']['visits_per_billing_period'], $this->mainFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['amount'], $this->totalRowStyles)->addText(format_money($salary['refunds_for_completed_progress_notes']['amount_paid']), $this->mainFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['notes'], $this->totalRowStyles)->addText('- included in this payment', $this->mainFontStyle);
    
            $grandTotal['visits_per_billing_period'] += $salary['refunds_for_completed_progress_notes']['visits_per_billing_period'];
            $grandTotal['amount_paid'] += $salary['refunds_for_completed_progress_notes']['amount_paid'];
            $table->addRow();
            $table->addCell()->getStyle()->setGridSpan(4);
        }
    
        if(!empty($salary['additional_compensation'])) {
            $table->addRow();
            $table->addCell($cellWidth['title'], $this->headerRowStyles)->addText('Additional Compensation', $this->tableHeaderFontStyle);
            $table->addCell($cellWidth['visits_count'], $this->headerRowStyles)->addText('# of Visits', $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['amount'], $this->headerRowStyles)->addText('Amount', $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['notes'], $this->headerRowStyles)->addText('Additional Comments', $this->tableHeaderFontStyle);
        
            foreach ($salary['additional_compensation'] as $additionalCompensation) {
                if($additionalCompensation['type_slug'] !== 'sick_time') {
                    $table->addRow();
                    $table->addCell($cellWidth['title'])->addText($additionalCompensation['title'], $this->mainFontStyle);
                    $table->addCell($cellWidth['visits_count'])->addText(data_get($additionalCompensation, 'additional_data.visit_count'), $this->mainFontStyle, $this->cellContentCenter);
                    $table->addCell($cellWidth['amount'])->addText(format_money($additionalCompensation['amount_paid']), $this->mainFontStyle, $this->cellContentCenter);
                    $table->addCell($cellWidth['notes'])->addText($additionalCompensation['notes'], $this->mainFontStyle);
                    $grandTotal['amount_paid'] += $additionalCompensation['amount_paid'];
                } else {
                    $sickTime = $additionalCompensation;
                }
            }
        }
        
        $table->addRow();
        $fontStyle = $this->getFontStyle(10, true);
        $table->addCell($cellWidth['title'], $this->totalRowStyles)->addText('Grand Total (total payment for this period)', $fontStyle);
        $table->addCell($cellWidth['visits_count'], $this->totalRowStyles)->addText('', $this->tableHeaderFontStyle, $fontStyle);
        $table->addCell($cellWidth['amount'], $this->totalRowStyles)->addText(format_money($grandTotal['amount_paid']), $fontStyle, $this->cellContentCenter);
        $table->addCell($cellWidth['notes'], $this->totalRowStyles)->addText('- total you will receive', $fontStyle);
    
        if(isset($sickTime)) {
            $table->addRow();
            $table->addCell($cellWidth['title'])->addText($sickTime['title'], $this->mainFontStyle);
            $table->addCell($cellWidth['visits_count'])->addText(data_get($sickTime, 'additional_data.visit_count'), $this->mainFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['amount'])->addText('', $this->mainFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['notes'])->addText($sickTime['notes'], $this->mainFontStyle);
        }
    }
    
    private function addSalaryDetails(Section $section, $salaryDetails, $cellWidth)
    {
        $section->addText('Detailed Compensation Report - Number of Visits', $this->getFontStyle(10, true), [
            'align' => 'center',
//            'space' => [
//                'before' => Converter::cmToTwip(1.25),
//            ]
        ]);
        $section->addText('per CPT Code / Insurance Company', $this->getFontStyle(10, true), [
            'align' => 'center',
            'space' => [
                'after' => Converter::cmToTwip(0.5),
            ],
        ]);
        
        $table = $section->addTable($this->mainTableStyles);
    
        if(!empty($salaryDetails['regular'])) {
            $table->addRow();
            $cell = $table->addCell();
            $cell->addText('Compensation for Visits with Completed Progress Notes / Initial Assessments (Current Pay Period)', $this->tableHeaderFontStyle, $this->cellContentCenter);
            $cell->getStyle()->setGridSpan(4);
            $table->addRow();
            $table->addCell($cellWidth['title'], $this->headerRowStyles)->addText('Insurance / Plan / CPT Codes', $this->tableHeaderFontStyle);
            $table->addCell($cellWidth['visits_count'], $this->headerRowStyles)->addText('# of Visits', $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['amount'], $this->headerRowStyles)->addText('Fee per visit', $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['notes'], $this->headerRowStyles)->addText('Amount Paid', $this->tableHeaderFontStyle, $this->cellContentCenter);
            $subTotal = [
                'visits_per_billing_period' => 0,
                'amount_paid' => 0,
            ];
            foreach ($salaryDetails['regular'] as $item) {
                $table->addRow();
                $table->addCell($cellWidth['title'])->addText($item['insurance'] . ', ' . $item['plan_name'] . ', ' . $item['procedure_code'] . ($item['is_telehealth'] ? ' (Telehealth)' : '') . ($item['is_overtime'] ? ' (Overtime)' : '') . ($item['is_created_from_timesheet'] ? ' (Added by Therapist)' : ''), $this->mainFontStyle);
                $table->addCell($cellWidth['visits_count'])->addText($item['visits_per_billing_period'], $this->mainFontStyle, $this->cellContentCenter);
                $table->addCell($cellWidth['amount'])->addText(format_money($item['paid_fee']), $this->mainFontStyle, $this->cellContentCenter);
                $table->addCell($cellWidth['notes'])->addText(format_money($item['amount_paid']), $this->mainFontStyle, $this->cellContentCenter);
                $subTotal['visits_per_billing_period'] += $item['visits_per_billing_period'];
                $subTotal['amount_paid'] += $item['amount_paid'];
            }
            $table->addRow();
            $table->addCell($cellWidth['title'], $this->totalRowStyles)->addText('Subtotal', $this->tableHeaderFontStyle);
            $table->addCell($cellWidth['visits_count'], $this->totalRowStyles)->addText($subTotal['visits_per_billing_period'], $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['amount'], $this->totalRowStyles);
            $table->addCell($cellWidth['notes'], $this->totalRowStyles)->addText(format_money($subTotal['amount_paid']), $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addRow();
            $table->addCell()->getStyle()->setGridSpan(4);
        }
    
        if(!empty($salaryDetails['missing_progress_notes'])) {
            $table->addRow();
            $cell = $table->addCell();
            $cell->addText('Compensation for Visits with Missing Progress Notes / Initial Assessments (Current Pay Period)', $this->tableHeaderFontStyle, $this->cellContentCenter);
            $cell->getStyle()->setGridSpan(4);
            $table->addRow();
            $table->addCell($cellWidth['title'], $this->headerRowStyles)->addText('Insurance / Plan / CPT Codes', $this->tableHeaderFontStyle);
            $table->addCell($cellWidth['visits_count'], $this->headerRowStyles)->addText('# of Visits', $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['amount'], $this->headerRowStyles)->addText('Fee per visit', $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['notes'], $this->headerRowStyles)->addText('Amount Paid', $this->tableHeaderFontStyle, $this->cellContentCenter);
            $subTotal = [
                'visits_per_billing_period' => 0,
                'amount_paid' => 0,
            ];
            foreach ($salaryDetails['missing_progress_notes'] as $item) {
                $table->addRow();
                $table->addCell($cellWidth['title'])->addText($item['insurance'] . ', ' . $item['plan_name'] . ', ' . $item['procedure_code'] . ($item['is_telehealth'] ? ' (Telehealth)' : '') . ($item['is_overtime'] ? ' (Overtime)' : '') . ($item['is_created_from_timesheet'] ? ' (Added by Therapist)' : ''), $this->mainFontStyle);
                $table->addCell($cellWidth['visits_count'])->addText($item['visits_per_billing_period'], $this->mainFontStyle, $this->cellContentCenter);
                $table->addCell($cellWidth['amount'])->addText(format_money($item['paid_fee']), $this->mainFontStyle, $this->cellContentCenter);
                $table->addCell($cellWidth['notes'])->addText(format_money($item['amount_paid']), $this->mainFontStyle, $this->cellContentCenter);
                $subTotal['visits_per_billing_period'] += $item['visits_per_billing_period'];
                $subTotal['amount_paid'] += $item['amount_paid'];
            }
            $table->addRow();
            $table->addCell($cellWidth['title'], $this->totalRowStyles)->addText('Total', $this->tableHeaderFontStyle);
            $table->addCell($cellWidth['visits_count'], $this->totalRowStyles)->addText($subTotal['visits_per_billing_period'], $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['amount'], $this->totalRowStyles);
            $table->addCell($cellWidth['notes'], $this->totalRowStyles)->addText(format_money($subTotal['amount_paid']), $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addRow();
            $table->addCell()->getStyle()->setGridSpan(4);
        }
    
        if(!empty($salaryDetails['refunds_for_completed_progress_notes'])) {
            $table->addRow();
            $cell = $table->addCell();
            $cell->addText('Balance due to Provider For Visits From Previous Pay Periods', $this->tableHeaderFontStyle, $this->cellContentCenter);
            $cell->getStyle()->setGridSpan(4);
            $table->addRow();
            $table->addCell($cellWidth['title'], $this->headerRowStyles)->addText('Insurance / Plan / CPT Codes', $this->tableHeaderFontStyle);
            $table->addCell($cellWidth['visits_count'], $this->headerRowStyles)->addText('# of Visits', $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['amount'], $this->headerRowStyles)->addText('Fee per visit', $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['notes'], $this->headerRowStyles)->addText('Amount Paid', $this->tableHeaderFontStyle, $this->cellContentCenter);
            $subTotal = [
                'visits_per_billing_period' => 0,
                'amount_paid' => 0,
            ];
            foreach ($salaryDetails['refunds_for_completed_progress_notes'] as $item) {
                $table->addRow();
                $table->addCell($cellWidth['title'])->addText($item['insurance'] . ', ' . $item['plan_name'] . ', ' . $item['procedure_code'] . ($item['is_telehealth'] ? ' (Telehealth)' : '') . ($item['is_overtime'] ? ' (Overtime)' : '') . ($item['is_created_from_timesheet'] ? ' (Added by Therapist)' : ''), $this->mainFontStyle);
                $table->addCell($cellWidth['visits_count'])->addText($item['visits_per_billing_period'], $this->mainFontStyle, $this->cellContentCenter);
                $table->addCell($cellWidth['amount'])->addText(format_money($item['paid_fee']), $this->mainFontStyle, $this->cellContentCenter);
                $table->addCell($cellWidth['notes'])->addText(format_money($item['amount_paid']), $this->mainFontStyle, $this->cellContentCenter);
                $subTotal['visits_per_billing_period'] += $item['visits_per_billing_period'];
                $subTotal['amount_paid'] += $item['amount_paid'];
            }
            $table->addRow();
            $table->addCell($cellWidth['title'], $this->totalRowStyles)->addText('Grand Total', $this->tableHeaderFontStyle);
            $table->addCell($cellWidth['visits_count'], $this->totalRowStyles)->addText($subTotal['visits_per_billing_period'], $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['amount'], $this->totalRowStyles);
            $table->addCell($cellWidth['notes'], $this->totalRowStyles)->addText(format_money($subTotal['amount_paid']), $this->tableHeaderFontStyle, $this->cellContentCenter);
        }
    }
    
    private function addLongSalaryDetails(Section $section, $salaryDetails)
    {
        $section->addText('Detailed Compensation Report', $this->getFontStyle(10, true), [
            'align' => 'center',
            'space' => [
                'before' => Converter::cmToTwip(1.25),
//                'after' => Converter::cmToTwip(0.5),
            ]
        ]);
        
        if(!empty($salaryDetails['regular'])) {
            $cellWidth = [
                'visit_date' => Converter::cmToTwip(2),
                'pos' => Converter::cmToTwip(1),
                'cpt' => Converter::cmToTwip(3),
                'insurance' => Converter::cmToTwip(4.5),
                'patient' => Converter::cmToTwip(5),
                'fee' => Converter::cmToTwip(3),
            ];
    
            $section->addText('Compensation for Visits with Completed Progress Notes / Initial Assessments (Current Pay Period)', $this->tableHeaderFontStyle, [
                'align' => 'center',
                'space' => [
                    'before' => Converter::cmToTwip(0.5),
                    'after' => Converter::cmToTwip(0.20),
                ],
            ]);
            
            $table = $section->addTable($this->mainTableStyles);
            $table->addRow();
            $table->addCell($cellWidth['visit_date'], $this->headerRowStyles)->addText('Visit Date',
                $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['pos'], $this->headerRowStyles)->addText('POS', $this->tableHeaderFontStyle,
                $this->cellContentCenter);
            $table->addCell($cellWidth['cpt'], $this->headerRowStyles)->addText('CPT', $this->tableHeaderFontStyle,
                $this->cellContentCenter);
            $table->addCell($cellWidth['insurance'], $this->headerRowStyles)->addText('Insurance',
                $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['patient'], $this->headerRowStyles)->addText('Patient',
                $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['fee'], $this->headerRowStyles)->addText('Fee', $this->tableHeaderFontStyle,
                $this->cellContentCenter);
    
            $row = 0;
            $total = [
                'visits'   => 0,
                'patients' => [],
                'fee'      => 0,
            ];
            foreach ($salaryDetails['regular'] as $item) {
                $table->addRow();
                $table->addCell($cellWidth['visit_date'])->addText(Carbon::parse($item['date'])->format('m/d/Y'),
                    $this->mainFontStyle, $this->cellContentCenter);
                $table->addCell($cellWidth['pos'])->addText($item['pos'], $this->mainFontStyle,
                    $this->cellContentCenter);
                $table->addCell($cellWidth['cpt'])->addText($item['procedure_code'] . ($item['is_telehealth'] ? ' (Telehealth)' : '') . ($item['is_overtime'] ? ' (Overtime)' : '') . ($item['is_created_from_timesheet'] ? ' (Added by Therapist)' : ''),
                    $this->mainFontStyle, $this->cellContentCenter);
                $table->addCell($cellWidth['insurance'])->addText($item['insurance'], $this->mainFontStyle,
                    $this->cellContentCenter);
                $table->addCell($cellWidth['patient'])->addLink(url("/chart/{$item['patient_id']}"),
                    $item['patient_name'], $this->mainFontStyle, $this->cellContentCenter);
                $table->addCell($cellWidth['fee'])->addText(format_money($item['paid_fee']), $this->mainFontStyle,
                    $this->cellContentCenter);
                $total['visits']++;
                $total['patients'][$item['patient_id']] = '';
                $total['fee'] += $item['paid_fee'];
            }
            $table->addRow();
            $table->addCell($cellWidth['visit_date'], $this->totalRowStyles);
            $table->addCell($cellWidth['pos'], $this->totalRowStyles);
            $table->addCell($cellWidth['cpt'], $this->totalRowStyles);
            $table->addCell($cellWidth['insurance'], $this->totalRowStyles);
            $table->addCell($cellWidth['patient'], $this->totalRowStyles)->addText(''/*count($total['patients'])*/, $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['fee'], $this->totalRowStyles)->addText(format_money($total['fee']), $this->tableHeaderFontStyle, $this->cellContentCenter);
        }
    
        if(!empty($salaryDetails['missing_progress_notes'])) {
        
            $section->addText('Compensation for Visits with Missing Progress Notes / Initial Assessments (Current Pay Period)',
                $this->tableHeaderFontStyle, [
                    'align' => 'center',
                    'space' => [
                        'before' => Converter::cmToTwip(0.5),
                        'after' => Converter::cmToTwip(0.20),
                    ],
                ]);
            $cellWidth = [
                'visit_date' => Converter::cmToTwip(2),
                'pos' => Converter::cmToTwip(1),
                'cpt' => Converter::cmToTwip(2),
                'insurance' => Converter::cmToTwip(3.5),
                'patient' => Converter::cmToTwip(4),
                'full_amount' => Converter::cmToTwip(2),
                'partial_amount' => Converter::cmToTwip(2),
                'balance' => Converter::cmToTwip(2),
            ];
            
            $table = $section->addTable($this->mainTableStyles);
            $table->addRow();
            $table->addCell($cellWidth['visit_date'], $this->headerRowStyles)->addText('Visit Date', $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['pos'], $this->headerRowStyles)->addText('POS', $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['cpt'], $this->headerRowStyles)->addText('CPT', $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['insurance'], $this->headerRowStyles)->addText('Insurance', $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['patient'], $this->headerRowStyles)->addText('Patient', $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['full_amount'], $this->headerRowStyles)->addText('Full Amount', $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['partial_amount'], $this->headerRowStyles)->addText('Partial (w/o PN)', $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['balance'], $this->headerRowStyles)->addText('Balance', $this->tableHeaderFontStyle, $this->cellContentCenter);
            $row = 0;
            $total = [
                'visits' => 0,
                'patients' => [],
                'full_amount' => 0,
                'partial_amount' => 0,
            ];
            foreach ($salaryDetails['missing_progress_notes'] as $item) {
                $table->addRow();
                $table->addCell($cellWidth['visit_date'])->addText(Carbon::parse($item['date'])->format('m/d/Y'), $this->mainFontStyle, $this->cellContentCenter);
                $table->addCell($cellWidth['pos'])->addText($item['pos'], $this->mainFontStyle, $this->cellContentCenter);
                $table->addCell($cellWidth['cpt'])->addText($item['procedure_code'] . ($item['is_telehealth'] ? ' (Telehealth)' : '') . ($item['is_overtime'] ? ' (Overtime)' : '') . ($item['is_created_from_timesheet'] ? ' (Added by Therapist)' : ''), $this->mainFontStyle, $this->cellContentCenter);
                $table->addCell($cellWidth['insurance'])->addText($item['insurance'], $this->mainFontStyle, $this->cellContentCenter);
                $table->addCell($cellWidth['patient'])->addLink(url("/chart/{$item['patient_id']}"), $item['patient_name'], $this->mainFontStyle, $this->cellContentCenter);
                $table->addCell($cellWidth['full_amount'])->addText(format_money($item['fee']), $this->mainFontStyle, $this->cellContentCenter);
                $table->addCell($cellWidth['partial_amount'])->addText(format_money($item['paid_fee']), $this->mainFontStyle, $this->cellContentCenter);
                $table->addCell($cellWidth['balance'])->addText(format_money($item['fee'] - $item['paid_fee']), $this->mainFontStyle, $this->cellContentCenter);
                $total['visits']++;
                $total['patients'][$item['patient_id']] = '';
                $total['full_amount'] += $item['fee'];
                $total['partial_amount'] += $item['paid_fee'];
            }
            $table->addRow();
            $table->addCell($cellWidth['visit_date'], $this->totalRowStyles);
            $table->addCell($cellWidth['pos'], $this->totalRowStyles);
            $table->addCell($cellWidth['cpt'], $this->totalRowStyles);
            $table->addCell($cellWidth['insurance'], $this->totalRowStyles);
            $table->addCell($cellWidth['patient'], $this->totalRowStyles)->addText(''/*count($total['patients'])*/, $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['full_amount'], $this->totalRowStyles)->addText(format_money($total['full_amount']), $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['partial_amount'], $this->totalRowStyles)->addText(format_money($total['partial_amount']), $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['balance'], $this->totalRowStyles)->addText(format_money($total['full_amount'] - $total['partial_amount']), $this->tableHeaderFontStyle, $this->cellContentCenter);
    
        }
        
        if(!empty($salaryDetails['refunds_for_completed_progress_notes'])) {
            $section->addText('Balance due to Provider For Visits From Previous Pay Periods', $this->tableHeaderFontStyle, [
                'align' => 'center',
                'space' => [
                    'before' => Converter::cmToTwip(0.5),
                    'after' => Converter::cmToTwip(0.20),
                ],
            ]);
            $cellWidth = [
                'visit_date' => Converter::cmToTwip(2),
                'pn_created_at' => Converter::cmToTwip(2.5),
                'pos' => Converter::cmToTwip(1),
                'cpt' => Converter::cmToTwip(2.5),
                'insurance' => Converter::cmToTwip(3),
                'patient' => Converter::cmToTwip(4.5),
                'fee' => Converter::cmToTwip(3),
            ];
            $table = $section->addTable($this->mainTableStyles);
            $table->addRow();
            $table->addCell($cellWidth['visit_date'], $this->headerRowStyles)->addText('Visit Date', $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['pn_created_at'], $this->headerRowStyles)->addText('PN / IA Created At', $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['pos'], $this->headerRowStyles)->addText('POS', $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['cpt'], $this->headerRowStyles)->addText('CPT', $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['insurance'], $this->headerRowStyles)->addText('Insurance', $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['patient'], $this->headerRowStyles)->addText('Patient', $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['fee'], $this->headerRowStyles)->addText('Fee', $this->tableHeaderFontStyle, $this->cellContentCenter);
            $row = 0;
            $total = [
                'visits' => 0,
                'patients' => [],
                'fee' => 0,
            ];
            foreach ($salaryDetails['refunds_for_completed_progress_notes'] as $item) {
                $table->addRow();
                $table->addCell($cellWidth['visit_date'])->addText(Carbon::parse($item['visit_date'])->format('m/d/Y'), $this->mainFontStyle, $this->cellContentCenter);
                $table->addCell($cellWidth['pn_created_at'])->addText(Carbon::parse($item['date'])->format('m/d/Y'), $this->mainFontStyle, $this->cellContentCenter);
                $table->addCell($cellWidth['pos'])->addText($item['pos'], $this->mainFontStyle, $this->cellContentCenter);
                $table->addCell($cellWidth['cpt'])->addText($item['procedure_code'] . ($item['is_telehealth'] ? ' (Telehealth)' : '') . ($item['is_overtime'] ? ' (Overtime)' : '') . ($item['is_created_from_timesheet'] ? ' (Added by Therapist)' : ''), $this->mainFontStyle, $this->cellContentCenter);
                $table->addCell($cellWidth['insurance'])->addText($item['insurance'], $this->mainFontStyle, $this->cellContentCenter);
                $table->addCell($cellWidth['patient'])->addLink(url("/chart/{$item['patient_id']}"), $item['patient_name'], $this->mainFontStyle, $this->cellContentCenter);
                $table->addCell($cellWidth['fee'])->addText(format_money($item['paid_fee']), $this->mainFontStyle, $this->cellContentCenter);
                $total['visits']++;
                $total['patients'][$item['patient_id']] = '';
                $total['fee'] += $item['paid_fee'];
            }
            $table->addRow();
            $table->addCell($cellWidth['visit_date'], $this->totalRowStyles);
            $table->addCell($cellWidth['pn_created_at'], $this->totalRowStyles);
            $table->addCell($cellWidth['pos'], $this->totalRowStyles);
            $table->addCell($cellWidth['cpt'], $this->totalRowStyles);
            $table->addCell($cellWidth['insurance'], $this->totalRowStyles);
            $table->addCell($cellWidth['patient'], $this->totalRowStyles)->addText(''/*count($total['patients'])*/, $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['fee'], $this->totalRowStyles)->addText(format_money($total['fee']), $this->tableHeaderFontStyle, $this->cellContentCenter);
    
        }
    
        if(!empty($salaryDetails['late_cancellations'])) {
            $section->addText('Late Appt. Cancellations', $this->tableHeaderFontStyle, [
                'align' => 'center',
                'space' => [
                    'before' => Converter::cmToTwip(0.5),
                    'after' => Converter::cmToTwip(0.20),
                ],
            ]);
            $cellWidth = [
                'visit_date' => Converter::cmToTwip(2),
                'fee_collected_at' => Converter::cmToTwip(3.3),
                'patient' => Converter::cmToTwip(7.2),
                'fee' => Converter::cmToTwip(3),
                'paid_fee' => Converter::cmToTwip(3),
            ];
            $table = $section->addTable($this->mainTableStyles);
            $table->addRow();
            $table->addCell($cellWidth['visit_date'], $this->headerRowStyles)->addText('Visit Date', $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['fee_collected_at'], $this->headerRowStyles)->addText('Fee Collected At', $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['patient'], $this->headerRowStyles)->addText('Patient', $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['fee'], $this->headerRowStyles)->addText('Collected Fee', $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['paid_fee'], $this->headerRowStyles)->addText('Paid Fee', $this->tableHeaderFontStyle, $this->cellContentCenter);
            $row = 0;
            $total = [
                'visits' => 0,
                'fee' => 0,
            ];
            foreach ($salaryDetails['late_cancellations'] as $item) {
                $table->addRow();
                $table->addCell($cellWidth['visit_date'])->addText(Carbon::parse($item['visit_date'])->format('m/d/Y'), $this->mainFontStyle, $this->cellContentCenter);
                $table->addCell($cellWidth['fee_collected_at'])->addText(Carbon::parse($item['date'])->format('m/d/Y') . ($item['is_custom_created'] ? ' (Added by Therapist)' : ''), $this->mainFontStyle, $this->cellContentCenter);
                $table->addCell($cellWidth['patient'])->addLink(url("/chart/{$item['patient_id']}"), $item['patient_name'], $this->mainFontStyle, $this->cellContentCenter);
                $table->addCell($cellWidth['fee'])->addText(format_money($item['collected_fee']), $this->mainFontStyle, $this->cellContentCenter);
                $table->addCell($cellWidth['paid_fee'])->addText(format_money($item['paid_fee']), $this->mainFontStyle, $this->cellContentCenter);
                $total['visits']++;
                $total['fee'] += $item['paid_fee'];
            }
            $table->addRow();
            $table->addCell($cellWidth['visit_date'], $this->totalRowStyles);
            $table->addCell($cellWidth['fee_collected_at'], $this->totalRowStyles);
            $table->addCell($cellWidth['patient'], $this->totalRowStyles)->addText(''/*count($total['patients'])*/, $this->tableHeaderFontStyle, $this->cellContentCenter);
            $table->addCell($cellWidth['fee'], $this->totalRowStyles);
            $table->addCell($cellWidth['paid_fee'], $this->totalRowStyles)->addText(format_money($total['fee']), $this->tableHeaderFontStyle, $this->cellContentCenter);
        
        }
    }

    public function generate($provider, $salary, $salaryDetails, $longSalaryDetails, $filters) {
        $phpWord = new PhpWord();
        $sectionStyle = [
            'marginRight' => Converter::cmToTwip(1.25),
            'marginLeft' => Converter::cmToTwip(1.25),
            'marginBottom' => Converter::cmToTwip(1.25),
        ];
        $section = $phpWord->addSection($sectionStyle);
        $cellWidth = [
            'title' => Converter::cmToTwip(9),
            'visits_count' => Converter::cmToTwip(2),
            'amount' => Converter::cmToTwip(2.5),
            'notes' => Converter::cmToTwip(5),
        ];
        $this->addLogo($section);
        $this->addHead($section, $provider);
        $reportDate = $this->addDates($section, $filters);
        $this->addOverviewHead($section, $provider);
        $this->addSalary($section, $salary, $cellWidth);
    
        $section = $phpWord->addSection($sectionStyle);
        
        $this->addSalaryDetails($section, $salaryDetails, $cellWidth);
        $this->addLongSalaryDetails($section, $longSalaryDetails);
        
        $reportDate = str_replace('/', '_', $reportDate);
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $fileName = $provider->provider_name . ' ' . $reportDate . '.docx';
        try {
            $objWriter->save(storage_path('app/salary_reports/' . $fileName));
        } catch (Exception $e) {
            \App\Helpers\SentryLogger::captureException($e);
        }
        $cookie = cookie('document-download', "true", 0.05, null, null, false, false);
        $mime = Storage::disk('salary_reports')->mimeType($fileName);
        return response(Storage::disk('salary_reports')->get($fileName), 200, [
            "Content-Type" => $mime,
            "Content-disposition" => "attachment; filename=\"" . $fileName . "\"",
        ])->cookie($cookie);
    }

}
