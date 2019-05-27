<?php

namespace Parsers;

use Utils\Curl;

/**
 * Class Base
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
     * @param string $url
     * @param string $path
     * @throws \Exception
     */
    public function download(string $url, string $path) {
        $curl = new Curl($url);
        $dir = dirname($path);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $this->dom = $curl->download($path);
    }

    /**
     * @param array $params
     * @param string|null $url
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
     * @return void
     * @throws \Exception
     */
    public function process() {
        foreach ($this->items as $item) {
            $state = strtolower($item['state']);
            $court = strtolower(str_replace(' ', '-', $item['court']));
            $path = __DIR__ . "/../documents/{$state}/{$court}/{$item['year']}/{$item['id']}.pdf";
            $this->download($item['file'], $path);
            // We could save this in a database, but for sample purposes, just print.
            file_put_contents("/tmp/{$state}.txt", var_export($item, true), FILE_APPEND);
        }
    }

    /**
     * @return mixed
     */
    public abstract function parse();
}
