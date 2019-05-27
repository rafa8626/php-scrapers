<?php

namespace Parsers;

/**
 * Class NorthCarolina
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
     *
     * @see parent::parse()
     * @return string[]
     */
    public function parse() {
        // 1) POST on form
        $years = range(1998, date('Y'));
        rsort($years);
        $months = range(1, 12);
        rsort($months);
        $days = [1, 30];

        foreach ($years as $year) {
            foreach ($months as $month) {
                $startDate = sprintf('%d02/%d02/%d04', $month, $days[0], $year);
                $endDate = sprintf('%d02/%d02/%d04', $month, $days[1], $year);
                
                $this->searchByMonth($startDate, $endDate);
                $this->_gatherItems($year);
            }
        }
    }

    /**
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
     * @return void
     * @param \DOMDocument|null $dom
     */
    private function _gatherItems(string $year) {
        print_r($this->dom); exit;
        $xpath = new \DOMXPath($this->dom);
        $items = $xpath->query('//tr[@class="hover"]/td/span');
        $list = [];
        foreach ($items as $item) {
            $main = $xpath->query('.//span[@class="title"]', $item);
            if (!$main->length) {
                continue;
            }
            $row = $main[0];
            preg_match('/(?P<title>.*?)\s+\((?P<id>.*?)\s+\-\s+Published\)/msi', $row->nodeValue, $matches);
            $pdf = preg_replace('/viewOpinion\("(.*?)"\)/ims', '$1', $row->getAttribute('onclick'));
            $list[] = [
                'id' => $matches['id'],
                'title' => preg_replace('/(^\s+)|(,\s+$)/', "", $matches['title']),
                'description' => $xpath->query('.//span[@class="desc"]', $item)[0]->nodeValue,
                'file' => $pdf,
                'court' => 'Supreme Court',
                'state' => 'NC',
                'year' => $year,
            ];
        }

        return $list;
    }
}