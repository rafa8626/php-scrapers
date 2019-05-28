<?php

namespace Parsers;

/**
 * NorthCarolina class
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
     * Gather current items and then load pages since 1998 to obtain archive.
     *
     * @see parent::parse()
     * @return void
     */
    public function parse() {
        $this->items = $this->_gatherItems(date('Y'));
        $years = range(1998, date('Y', strtotime('-1 year')));
        rsort($years);
        $baseUrl = 'https://appellate.nccourts.org/opinion-filings/?c=sc&year=%d';
        foreach ($years as $year) {
            try {
                echo "\n\nGathering items from year {$year}\n";
                $this->load(sprintf($baseUrl, $year));
                $this->items = array_merge($this->items, $this->_gatherItems($year));
            } catch (\Exception $e) {
                die($e->getMessage());
            }
        }
    }

    /**
     *
     *
     * @param string $year
     * @return string[]
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
            $row = $main->item(0);
            preg_match('/(?P<title>.*?)\s+\((?P<id>[\w-,]+)\s+\-\s+Published\)/msi', $row->nodeValue, $matches);
            $pdf = preg_replace('/viewOpinion\("(.*?)"\)/ims', '$1', $row->getAttribute('onclick'));
            $title = preg_replace('/(^\s+)|(,\s+$)/', "", $matches['title']);
            echo "\t{$matches['id']} - {$title}\n";
            $list[] = [
                'id' => $matches['id'],
                'title' => $title,
                'description' => $xpath->query('.//span[@class="desc"]', $item)[0]->nodeValue,
                'file' => $pdf,
                'extension' => 'pdf',
                'court' => 'Supreme Court',
                'state' => 'NC',
                'year' => $year,
            ];
        }

        return $list;
    }
}