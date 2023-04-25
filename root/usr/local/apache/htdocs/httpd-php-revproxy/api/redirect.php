<?php

require "../lib/autoload.php";
$config = new RevProxy_Config(CONFIG_ROOT);
if (($message = RevProxy_Login::ensureAuthenticated($config)) !== true) {
    echo $message;
    exit;
}

$classes = array();

try {
	if (!isset($_GET['uuid'])) {
		throw new Exception("Did not provide uuid");
	}

	$api = new RevProxy_API($config);
	$redirects = $api->getRedirects();

	if (!isset($redirects[$_GET['uuid']]))
	{
		throw new Exception("Invalid uuid");
	}

	$dirty = FALSE;
	if (isset($_GET['enabled'])) {
		if (!preg_match('/\\A(?:true|false)\\z/i', $_GET['enabled'])) {
			throw new Exception('Enabled is not valid!');
		}
		$redirects[$_GET['uuid']]['enabled'] = ($_GET['enabled'] === 'true');
		$dirty = TRUE;
	}

	if (isset($_GET['source'])) {
		if (!preg_match('/\\A(?:[[:alnum:]][[:alnum:]\\-]*\\.?)*[[:alnum:]]\\z/i', $_GET['source'])) {
			throw new Exception('Source hostname is not valid!');
		}
		if (ip2long($_GET['source']) !== FALSE) {
			throw new Exception('Source Hostname cannot not be an IP address!');
		}
		if ($_GET['source'] === php_uname('n') || $_GET['source'] === $_SERVER['HTTP_HOST']) {
			throw new Exception("Cannot set source hostname to this machine's hostname or you'll prevent access to this interface!");
		}
		$redirects[$_GET['uuid']]['source'] = $_GET['source'];
		$dirty = TRUE;
	}

	if (isset($_GET['destination'])) {
		if (ip2long($_GET['destination']) === FALSE && !preg_match('/\\A(?:[[:alnum:]][[:alnum:]\\-]*\\.?)*[[:alnum:]]\\z/i', $_GET['destination'])) {
			throw new Exception('Destination must be an IP address or a valid hostname!');
		}
		if (ip2long($_GET['destination']) === FALSE && gethostbyname($_GET['destination']) === $_GET['destination']) {
			throw new Exception('Destination hostname cannot be resolved!');
		}
		if ($_GET['destination'] === php_uname('n') || $_GET['destination'] === $_SERVER['HOSTNAME']) {
			throw new Exception("Cannot set destination hostname to this machine's hostname!");
		}
		$redirects[$_GET['uuid']]['destination'] = $_GET['destination'];
		$dirty = TRUE;
	}
	if ($dirty)
	{
		$redirects[$_GET['uuid']]['modified'] = time();
	}

	$api->setRedirects($redirects);
	
}
catch(Exception $e) {
    echo json_encode(array(
        'error' => "Exception: {$e->getMessage() }"
    ));
    exit;
}
echo json_encode($redirects[$_GET['uuid']]);
?>
