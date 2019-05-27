<?php

namespace Parsers;

/**
 * Class NorthCarolina
 * @package Parsers
 */
class NorthCarolina extends Base {
    /**
     * NorthCarolina constructor.
     *
     * @throws \Exception
     */
    public function __construct() {
        parent::__construct('https://appellate.nccourts.org/opinion-filings/?c=sc');
    }

    /**
     *
     * @see parent::parse()
     * @return string[]
     */
    public function parse() {
        // 1) Gather most recent ones
        $this->items = $this->_gatherItems(date('Y'));

        // 2) find form to start download items per year
        $years = range(1998, date('Y', strtotime('-1 year')));
        rsort($years);
        $baseUrl = 'https://appellate.nccourts.org/opinion-filings/?c=sc&year=%d';
        foreach ($years as $year) {
            $this->load(sprintf($baseUrl, $year));
            $this->items = array_merge($this->items, $this->_gatherItems($year));
        }
    }

    /**
     *
     * @param \DOMDocument|null $dom
     */
    private function _gatherItems(string $year) {
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