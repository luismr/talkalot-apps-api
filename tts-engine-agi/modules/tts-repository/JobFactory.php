<?

if (!defined('__ROOT__')) {
	define('__ROOT__', dirname(dirname(dirname(__FILE__))));
}

require_once("Job.php");

class JobFactory {
	static protected $instance = null;

	static function getInstance($license, $key) {
		if (is_null(static::$instance)) {
			static::$instance = new JobFactory("https://api.ligflat.com.br/v2/tts", $license, $key);
		}

		return static::$instance;
	}

	static function getInstance($endpoint, $license, $key) {
		if (is_null(static::$instance)) {
			static::$instance = new JobFactory($endpoint, $license, $key);
		}

		return static::$instance;
	}

	private $license;
	private $key;
	private $endpoint = "https://api.ligflat.com.br/v2/tts";

	final private function __construct($endpoint, $license, $key) {
		$this->license = $license;
		$this->key = $key;
		$this->endpoint = $endpoint;
	}

	public function createJob($language, $gender, $text) {
		$job = new Job($language, $gender, $text, $this->license, $this->key);
		$job->setTtsEngineEndpoint($this->endpoint);

		return $job;
	}

}

?>
