<?php

namespace App\Traits\Parsers\OfficeAlly;

use Symfony\Component\DomCrawler\Crawler;
use Psy\Exception\BreakException;

trait TableProcessing
{
    private function getColumnsMappingWithIndexes(array $columnsMappingTemplate, Crawler $tableHeaders)
    {
        foreach ($columnsMappingTemplate as $key => $value) {
            $columnsMappingTemplate[$key]['index'] = $this->getColumnIndex($value['name'], $tableHeaders);
        }

        return $columnsMappingTemplate;
    }

    private function getColumnIndex(string $columnName, Crawler $tableHeaders)
    {
        $columnIndex = null;
        $currentIndex = 0;
        try {
            $tableHeaders->each(function ($node) use ($columnName, &$columnIndex, &$currentIndex) {
                $nodeText = remove_nbsp($node->text());

                if ($nodeText === $columnName) {
                    $columnIndex = $currentIndex;
                    throw new BreakException();
                }

                $colspan = intval($node->attr('colspan'));
                if ($colspan) {
                    $currentIndex += $colspan;
                } else {
                    $currentIndex += 1;
                }
            });

            $columnIndex;
        } catch (BreakException $e) {
            return $columnIndex;
        }
    }

    private function getMissedRequiredColumns(array &$columnsMapping)
    {
        $res = [];

        foreach ($columnsMapping as $key => $value) {
            if ($value['required'] && is_null($value['index'])) {
                $res[$key] = $value;
            }
        }

        return $res;
    }

    private function getStringVal(string $key, $rowNode, array &$columnsMapping)
    {
        $text = $this->getColumnText($key, $rowNode, $columnsMapping);
        return empty($text) ? null : remove_nbsp($text);
    }

    private function getIntVal(string $key, $rowNode, array &$columnsMapping)
    {
        $text = $this->getColumnText($key, $rowNode, $columnsMapping);
        return empty($text) ? null : intval($text);
    }

    private function getFloatVal(string $key, $rowNode, array &$columnsMapping)
    {
        $text = $this->getColumnText($key, $rowNode, $columnsMapping);
        return empty($text) ? null : floatval($text);
    }

    private function getColumnText(string $key, $rowNode, array &$columnsMapping)
    {
        if (isset($columnsMapping[$key]) && isset($columnsMapping[$key]['index'])) {
            return $rowNode->children()->eq($columnsMapping[$key]['index'])->text();
        }

        return null;
    }
}
