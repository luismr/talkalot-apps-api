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
require_once(__ROOT__ . "/modules/tts-repository/RepositoryService.php");

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

try {
	$service = RepositoryService::getInstance($licence, $key);
	$job = $service->createJob($language, $gender, $text);
	
	if (! $job->isAvailable()) {
		$job->perform();
	}

	$agi->stream_file($job->getFilename());
} catch (Exception $e) {
	die("An exception was detected [" . $e . "]");	
}

?>
