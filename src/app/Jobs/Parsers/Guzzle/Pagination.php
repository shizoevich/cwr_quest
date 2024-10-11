<?php

namespace App\Jobs\Parsers\Guzzle;

use Symfony\Component\DomCrawler\Crawler;

trait Pagination
{
    /**
     * @param Crawler $crawler
     *
     * @return int
     */
    private function getPagesCount(Crawler $crawler)
    {
        $count = $crawler->filterXPath("//tr[@class='GridviewPager']/td/font/table/tr[last()]/td[last()]");
        if($count->count() > 0) {
            return (int)$count->text();
        }
        return 0;
    }

    /**
     * Updated version of the method "getPagesCount", it has new xpath selector
     * 
     * @param Crawler $crawler
     *
     * @return int
     */
    private function getPagesCountWithChangedXPath(Crawler $crawler)
    {
        $count = $crawler->filterXPath("//tr[@class='GridviewPager']/td/table/tr[last()]/td[last()]");
        if($count->count() > 0) {
            return (int)$count->text();
        }
        return 0;
    }
}