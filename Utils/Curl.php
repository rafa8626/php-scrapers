<?php

namespace Utils;

class Curl {
    /**
     * @var resource
     */
	private $curl = null;

    /**
     * @var int
     */
	private $timeout = 5;

    /**
     *
     * @param string $url
     * @return curl
     * @throws \Exception
     */
	public function __construct(string $url) {
   	    if (!filter_var($url, FILTER_VALIDATE_URL)) {
			throw new \Exception('No valid URL');
		}

    	$this->curl = curl_init($url);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curl, CURLOPT_HEADER, 0);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($this->curl,CURLOPT_USERAGENT,
            'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

        return $this->curl;
    }

    /**
     * Download URL and return object to obtain more info.
     *
     * @return \DOMDocument
     */
    public function load() {
		$html = curl_exec($this->curl);
		curl_close($this->curl);

		$dom = new \DOMDocument();
		libxml_use_internal_errors(true);
		$dom->loadHTML($html);
		return $dom;
    }

    /**
     * @param string[] $params
     * @return \DOMDocument
     */
    public function post(array $params) {
        curl_setopt($this->curl, CURLOPT_POST, 1);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, http_build_query($params));
        return $this->load();
    }

    /**
     *
     * @param string $path
     * @return void
     */
    public function download(string $path) {
        $fp = fopen($path, 'w+');
        curl_setopt($this->curl, CURLOPT_FILE, $fp);
        curl_exec($this->curl);
        curl_close($this->curl);
        fclose($fp);
    }
}
