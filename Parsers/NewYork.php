<?php

namespace Parsers;

/**
 * NewYork class
 * @package Parsers
 */
class NewYork extends Base {
    /**
     * NorthCarolina constructor.
     *
     * @throws \Exception
     */
    public function __construct() {
        parent::__construct('http://iapps.courts.state.ny.us/lawReporting/Search?searchType=opinion');
    }

    /**
     * Search decision per year per month (using start and end dates).
     *
     * @see parent::parse()
     * @return void
     */
    public function parse() {
        $years = range(1998, date('Y'));
        rsort($years);
        $months = range(1, 12);
        rsort($months);
        $this->items = [];

        foreach ($years as $year) {
            foreach ($months as $month) {
            	if ($year >= date('Y') && $month > date('n')) {
            		continue;
	            }
                $startDate = sprintf('%02d/%02d/%d', $month, 1, $year);
                $endDate = sprintf('%02d/%02d/%d', $month, date('j'), $year);
                echo "\n\nGathering items from {$startDate} to {$endDate}\n";

                try {
                    $this->searchByMonth($startDate, $endDate);
                    $this->items = array_merge($this->items, $this->_gatherItems($year));
                } catch (\Exception $e) {
                    die($e->getMessage());
                }
            }
        }
    }

    /**
     * Execute POST call to retrieve decisions per month.
     *
     * @param string $startDate
     * @param string $endDate
     * @return void
     * @throws \Exception
     */
    private function searchByMonth(string $startDate, string $endDate) {
        $params = [
            'rbOpinionMotion' => 'opinion',
            'and_or' => 'and',
            'dtStartDate' => $startDate,
            'dtEndDate' => $endDate,
            'court' => 'Court of Appeals',
            'and_or2' => 'and',
            'Order_By' => 'Party Name',
            'Submit' => 'Find',
        ];

        $this->post($params);
    }

    /**
     *
     *
     * @param string $url
     * @return string[]
     */
    private function _gatherItems(string $year) {
        $xpath = new \DOMXPath($this->dom);
        $items = $xpath->query('//table/tr[@valign="top"]/td[@width="10%"]');
        $list = [];
        foreach ($items as $item) {
            $fileTarget = $item->nextSibling->nextSibling
                ->nextSibling->nextSibling
                ->nextSibling->nextSibling
                ->nextSibling->nextSibling;

            $file = $xpath->query('.//a', $fileTarget->firstChild);
            preg_match('/^\d+.*?NYSlipOp.*?(?P<id>\d+)$/msi', $fileTarget->nodeValue, $matches);
            $title = $item->nodeValue;
            echo "\t{$matches['id']} - {$title}\n";
            $list[] = [
                'id' => $matches['id'],
                'title' => $title,
                'description' => '',
                'file' => $file->item(0)->getAttribute('href'),
                'extension' => 'html',
                'court' => 'Court of Appeals',
                'state' => 'NY',
                'year' => $year,
            ];
        }

        return $list;
    }
}