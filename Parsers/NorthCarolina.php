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
        $this->items = $this->_gatherItems();
        $years = range(1998, date('Y', strtotime('-1 year')));
        rsort($years);
        $baseUrl = 'https://appellate.nccourts.org/opinion-filings/?c=sc&year=%d';
        foreach ($years as $year) {
            try {
                echo "\n\nGathering items from year {$year}\n";
                $this->load(sprintf($baseUrl, $year));
                $this->items = array_merge($this->items, $this->_gatherItems());
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
    private function _gatherItems() {
        $xpath = new \DOMXPath($this->dom);
        $items = $xpath->query('//tr/td');
        $list = [];
        foreach ($items as $item) {
            $dateTarget = $xpath->query('.//strong', $item);
            if ($dateTarget->length) {
                preg_match('/filed:\s+(?P<id>\d{1,2}\s+[a-zA-Z]+\s+\d{4})\s*\n/msi', trim($dateTarget->item(0)->nodeValue), $match);
                $date = \DateTime::createFromFormat('j F Y', $match['id'])->format('Y-m-d');
            }

            $main = $xpath->query('.//span[@class="title"]', $item);
            if (!$main->length) {
                continue;
            }
            $row = $main->item(0);
            preg_match('/(?P<title>.*?)\s+\((?P<id>[\w-,]+)\s+\-\s+Published\)/msi', $row->nodeValue, $matches);
            $pdf = preg_replace('/viewOpinion\("(.*?)"\)/ims', '$1', $row->getAttribute('onclick'));
            $title = trim($matches['title'], ' ,');
            echo "\t{$matches['id']} - {$title}\n";
            $list[] = [
                'id' => $matches['id'],
                'title' => $title,
                'description' => $xpath->query('.//span[@class="desc"]', $item)[0]->nodeValue,
                'file' => $pdf,
                'extension' => 'pdf',
                'court' => 'Supreme Court',
                'state' => 'NC',
                'date' => $date,
            ];
        }

        return $list;
    }
}