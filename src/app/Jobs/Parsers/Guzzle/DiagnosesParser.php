<?php

namespace App\Jobs\Parsers\Guzzle;

use App\DTO\OfficeAlly\DiagnosesDTO;
use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Models\Diagnose;
use App\Option;
use Carbon\Carbon;
use Symfony\Component\DomCrawler\Crawler;

class DiagnosesParser extends AbstractParser
{
    use Pagination;

    /** @var OfficeAllyHelper */
    private $officeAllyHelper;

    private $viewState = null;

    private $viewStateGenerator = null;

    public function __construct()
    {
        $this->officeAllyHelper = app()->make(OfficeAllyHelper::class)(Option::OA_ACCOUNT_1);
        parent::__construct();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handleParser()
    {
        $searchQueries = [
            'F',
            'Z',
        ];
        foreach ($searchQueries as $searchQuery) {
            $this->recursiveParseDiagnoses($searchQuery, 1, null);
        }
    }

    private function recursiveParseDiagnoses(string $baseQuery, int $page = 1, $queryPostfix = null, $viewState = null, $viewStateGenerator = null)
    {
        $crawler = $this->getDiagnosesCrawler($baseQuery . $queryPostfix, $page, $viewState, $viewStateGenerator);
        $pagesCount = $this->getPagesCount($crawler);
        $processedDiagnoses = $this->processingDiagnoses($crawler);
        if ($page === 1 && $pagesCount > 1 && $processedDiagnoses < 10) {
            $this->recursiveParseDiagnoses($baseQuery, $page, $queryPostfix, $viewState, $viewStateGenerator);
            return;
        }
        if ($pagesCount >= 10) {
            for ($i = 0; $i <= 9; $i++) {
                $this->recursiveParseDiagnoses($baseQuery, 1, $queryPostfix === null ? $i : $queryPostfix . $i, $viewState, $viewStateGenerator);
            }
        } else if ($page < $pagesCount) {
            $this->recursiveParseDiagnoses($baseQuery, ++$page, $queryPostfix, $viewState, $viewStateGenerator);
        }
    }

    /**
     * @param Crawler $crawler
     *
     * @return int
     */
    private function processingDiagnoses(Crawler $crawler)
    {
        $parsedDiagnoses = 0;
        $crawler->filter('table#ctl04_popupBase_grvPopup td script')->each(function ($node) use (&$parsedDiagnoses, &$d) {
            $diagnoseData = json_decode($node->text(), true);

            // Diagnose::query()->updateOrCreate([
            //     'code' => trim($diagnoseData['Code'])
            // ], [
            //     'description' => trim($diagnoseData['Description']),
            //     'hcc' => trim($diagnoseData['HCC']) === 'Y' ? 1 : 0,
            //     'is_billable' => trim($diagnoseData['Billable']) === 'Yes' ? 1 : 0,
            //     'terminated_at' => trim($diagnoseData['TerminatedDate']) ? Carbon::parse($diagnoseData['TerminatedDate'])->toDateString() : null,
            // ]);

            $diagnoseDTO = new DiagnosesDTO([
                'code' => trim($diagnoseData['Code']),
                'description' => trim($diagnoseData['Description']),
                'hcc' => trim($diagnoseData['HCC']) === 'Y' ? 1 : 0,
                'is_billable' => trim($diagnoseData['Billable']) === 'Yes' ? 1 : 0,
                'terminated_at' => trim($diagnoseData['TerminatedDate']) ? Carbon::parse($diagnoseData['TerminatedDate'])->toDateString() : null,
            ]);

            Diagnose::query()->updateOrCreate([
                'code' => $diagnoseDTO->code
            ], [
                'description' => $diagnoseDTO->description,
                'hcc' => $diagnoseDTO->hcc,
                'is_billable' => $diagnoseDTO->is_billable,
                'terminated_at' => $diagnoseDTO->terminated_at,
            ]);
        });

        return $parsedDiagnoses;
    }

    /**
     * @param string $searchQuery
     * @param int    $page
     *
     * @return Crawler
     */
    private function getDiagnosesCrawler(string $searchQuery, int $page = 1, &$viewState = null, &$viewStateGenerator = null)
    {
        $diagnosisPage = $this->officeAllyHelper->getDiagnosisList($searchQuery, $page, $viewState, $viewStateGenerator);
        $crawler = new Crawler($diagnosisPage);
        $viewState = $crawler->filter('input[name=__VIEWSTATE]')->first()->attr('value');
        $viewStateGenerator = $crawler->filter('input[name=__VIEWSTATEGENERATOR]')->first()->attr('value');

        return $crawler;
    }
}
