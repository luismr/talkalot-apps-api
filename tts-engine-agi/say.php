#!/usr/bin/php -q
<?

/*
 * Configuration Settings
 */

$licence = "YORLICENCEHERE";
$key = "YOURLICENSEKEYHERE";

/*
 * Do not edit after this
 */

if (!defined('__ROOT__')) {
	define('__ROOT__', dirname(__FILE__));
}

require_once(__ROOT__ . "/lib/phpagi/phpagi.php");
require_once(__ROOT__ . "/modules/tts-repository/JobFactory.php");

$agi = new AGI();

$args = $_SERVER["argv"];

$language = $args[1];
if ($language == null || trim($language) == "") {
	die("Language [" . $language . "] is invalid!");
}

$gender = $args[2];
if ($gender == null || trim($gender) == "") {
	die("Gender [" . $gender . "] is invalid!");
}

$text = $args[3];
if ($text == null || trim($text) == "") {
	die("Text [" . $text . "] is invalid!");
}

$post = $args[4];
if ($post != null) {
	$post = ($post == "true") ? true : false;
} else {
	$post = false;
}

$devel = $args[5];
if ($devel != null) {
	$devel = ($devel == "true") ? true : false;
} else {
	$devel = false;
}

$agi->verbose("TTS Call [" . $language . "|" . $gender . "|" . $text . " | " . (($post) ? "TRUE" : "FALSE") . " | " . (($devel) ? "TRUE" : "FALSE") . "]");

try {
	$factory = JobFactory::getInstance($licence, $key);
	
	$job = $factory->createJob($language, $gender, $text);

	$agi->verbose("Job [" . $job->getName() . "]->isAvailable() == " . (($job->isAvailable()) ? "TRUE" : "FALSE"));
	if (! $job->isAvailable()) {
		if ($devel) {
			$job->setTtsEngineEndpoint("http://localhost:8080/tts-engine/tts");
		}
		
		$job->setForcedPost($post);
		$job->perform();
	}

	$agi->stream_file($job->getFilename());
} catch (Exception $e) {
	$msg = "An exception was detected [" . $e . "]";
	$agi->verbose($msg);
	die($msg);	
}

?>
