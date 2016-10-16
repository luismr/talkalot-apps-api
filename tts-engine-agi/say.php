#!/usr/bin/php -q
<?

/*
 * Configuration Settings
 */

$licence = "YORLICENCEHERE";
$key = "YOURLICENSEKEYHERE";

/*
 * Editjust if you have our ENGINE in INHOUSE mode
 *     DEFAULT -> means default ;-)
 *     some valid http/https URL such as http://some.domain.com/v2/tts
 */

$endpoint = "DEFAULT";

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

if ( $endpoint != "DEFAULT" && filter_var($endpoint, FILTER_VALIDATE_URL) === false) {
	die("Endpoint [" . $endpoint . "] is invalid!");
} else {
	if ($devel) {
		$endpoint = "http://localhost:8080/tts";
	} else {
		$endpoint = "https://api.ligflat.com.br/v2/tts";
	}
}

try {
	$factory = JobFactory::getInstance($endpoint, $licence, $key);
	$job = $factory->createJob($language, $gender, $text);

	if (! $job->isAvailable()) {
		$job->setForcedPost($post);
		$job->perform();
	}

	$rc = $agi->stream_file($job->getFilename(), AST_DIGIT_ANY);
	$digit = chr($rc['result']);

	if (strpos(AST_DIGIT_ANY, $digit) !== false) {
		$agi->set_variable("SAY_DIGIT", $digit);
	}
} catch (Exception $e) {
	$msg = "An exception was detected [" . $e . "]";
	$agi->verbose($msg);
	die($msg);
}

?>
