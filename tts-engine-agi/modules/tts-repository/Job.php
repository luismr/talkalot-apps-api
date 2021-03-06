<?php

if (!defined('__ROOT__')) {
	define('__ROOT__', dirname(dirname(dirname(__FILE__))));
}

require_once(__ROOT__ . "/lib/rest-client/rest-curl-client.php");

class Job {

	private $language;
	private $gender;
	private $text;

	private $license;
	private $key;

	private $name;
	private $filename;
	private $fileMp3;
	private $fileWav;
	private $pathRepository;
	private $forcedPost;

	private $ttsEngineEndpoint;

	public function __construct($language, $gender, $text, $license, $key) {
		$this->language = $language;
		$this->gender = $gender;
		$this->text = $text;

		$this->license = $license;
		$this->key = $key;

		$this->ttsEngineEndpoint = "https://api.ligflat.com.br/v2/tts";
		$this->forcedPost = false;

		$this->pathRepository = sys_get_temp_dir();

		$this->generateName();
	}

	public function setTtsEngineEndpoint($endpoint) {
		$this->ttsEngineEndpoint = $endpoint;
	}

	public function getTtsEngineEndpoint() {
		return $this->ttsEngineEndpoint;
	}

	public function setForcedPost($forced) {
		$this->forcedPost = $forced;
	}

	public function isForcedPost() {
		return $this->forcedPost;
	}

	public function isAvailable() {
		$available = $this->isMp3Available() && $this->isWavAvailable();
		return $available;
	}

	public function getName() {
		return $this->name;
	}

	public function getFilename() {
		return $this->filename;
	}

	public function getMp3() {
		return ($this->isMp3Available()) ? $this->fileMp3 : null;
	}

	public function getWav() {
		return ($this->isWavAvailable()) ? $this->fileWav : null;
	}

	public function perform() {
		if (!$this->isAvailable()) {
			if ($this->isMp3Available()) {
				$this->convertJobAudioFormat();
			} else {
				$this->downloadMp3();
				$this->convertJobAudioFormat();
			}
		}
	}

	private function generateName() {
		$tmp = $this->language . "="
				. $this->gender . "-"
						. $this->text;

		$this->name = md5($tmp);
		$this->filename = $this->pathRepository . DIRECTORY_SEPARATOR . $this->name;
		$this->fileMp3 = $this->filename . ".mp3";
		$this->fileWav = $this->filename . ".sln";
	}

	private function convertJobAudioFormat() {
		$sox = (file_exists("/usr/bin/sox")) ? "/usr/bin/sox" :
					((file_exists("/usr/local/bin/sox")) ? "/usr/local/bin/sox" : null);

		if ($sox == null) {
			throw new Exception("Sox not available!");
		}

		$cmd = $sox . " " . $this->fileMp3 . " -t raw -r 8000 -s -2 -c 1 " . $this->fileWav;
		syslog(LOG_INFO, "Job [" . $this->name . "] SOX convert command line: " . $cmd);

		exec($cmd);
	}

	private function downloadMp3() {
		$options = array(
				CURLOPT_HTTPHEADER => array(
						"X-LigFlat-TTS-Licence: " . $this->license,
						"X-LigFlat-TTS-Key: " . $this->key
				)
		);

		if (!$this->isForcedPost() || strlen($this->text) > 32) {
			$data = $this->downloadByHttpPost($options);
		} else {
			$data = $this->downloadByHttpGet($options);
		}

		$workfile = fopen($this->fileMp3, "w+");
		fputs($workfile, $data);
		fclose($workfile);
	}

	private function downloadByHttpGet($options) {
		$url = $this->ttsEngineEndpoint . "/say/" . $this->language . "/" . $this->gender . "/" . urlencode($this->text);

		$client = new RestCurlClient();
		$data = $client->get($url, $options);

		return $data;
	}

	private function downloadByHttpPost($options) {
		$url = $this->ttsEngineEndpoint . "/say";

		$fields = array(
				"text" => $this->text,
				"language" => $this->language,
				"gender" => $this->gender
		);

		$client = new RestCurlClient();
		$data = $client->post($url, $fields, $options);

		return $data;
	}

	private function isMp3Available() {
		return file_exists($this->fileMp3);
	}

	private function isWavAvailable() {
		return file_exists($this->fileWav);
	}
}

?>
