<?

if (!defined('__ROOT__')) {
	define('__ROOT__', dirname(dirname(dirname(__FILE__))));
}

require_once("Job.php");

class RepositoryService {
	static protected $instance = null;

	static function getInstance($license, $key) {
		if (is_null(static::$instance)) {
			static::$instance = new RepositoryService($license, $key);
		}

		return static::$instance;
	}

	private $license;
	private $key;
	
	final private function __construct($license, $key) {
		$this->license = $license;
		$this->key = $key;
	}
		
	public function createJob($language, $gender, $text) {
		$job = new Job($language, $gender, $text, $this->license, $this->key);
		return $job;
	}
	
}

?>