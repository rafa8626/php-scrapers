<?php

namespace Parsers;

use Utils\Curl;

/**
 * Base class.
 *
 * @package Parsers
 */
abstract class Base {
    /**
     * @var string[]
     */
    protected $items;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var \DOMDocument
     */
    protected $dom;

    /**
     * @var bool
     */
    protected $grabAll = false;

    /**
     * Base constructor
     *
     * @param string $url
     * @throws \Exception
     */
    public function __construct(string $url) {
        $this->url = $url;
        $this->load();
    }

    /**
     * Load HTML output from a URL.
     *
     * @param string|null $url
     * @return void
     * @throws \Exception
     */
    public function load(string $url = null) {
        try {
            $curl = new Curl($url ?? $this->url);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        $this->dom = $curl->load();
    }

    /**
     * Download a file under a specified location.
     *
     * @param string $url
     * @param string $path
     * @return void
     * @throws \Exception
     */
    public function download(string $url, string $path) {
        $curl = new Curl($url);
        $dir = dirname($path);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $curl->download($path);
    }

    /**
     * Execute a POST cURL call to obtain DOM.
     *
     * @param string[] $params
     * @param string|null $url
     * @return void
     * @throws \Exception
     */
    public function post(array $params, string $url = null) {
        try {
            $curl = new Curl($url && $url !== $this->url ? $url : $this->url);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        $this->dom = $curl->post($params);
    }

    /**
     * Download file from URL and store data in a file.
     *
     * This method can be adjusted to use a database to save records.
     * @return int
     * @throws \Exception
     */
    public function process() {
    	$saved = 0;
        foreach ($this->items as $item) {
            $state = strtolower($item['state']);
            $court = strtolower(str_replace(' ', '-', $item['court']));
            $path = __DIR__ . "/../documents/{$state}/{$court}/{$item['year']}/{$item['id']}.{$item['extension']}";
            file_put_contents("/tmp/{$state}.txt", var_export($item, true), FILE_APPEND);

            // Avoid saving it if file exists already
            if (file_exists($path)) {
            	continue;
            }
            $this->download($item['file'], $path);
            ++$saved;
        }

        return $saved;
    }

    /**
     * Parser callback, different for each inherited class.
     *
     * @return mixed
     */
    public abstract function parse();
}
