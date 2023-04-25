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
	$creds = $api->getCredentials();

	if (!isset($creds[$_GET['uuid']]))
	{
		throw new Exception("Invalid uuid");
	}

	$dirty = FALSE;
	if (isset($_GET['enabled'])) {
		if (!preg_match('/\\A(?:true|false)\\z/i', $_GET['enabled'])) {
			throw new Exception('Enabled is not valid!');
		}
		$creds[$_GET['uuid']]['enabled'] = ($_GET['enabled'] === 'true');
		$dirty = TRUE;
	}

	if (isset($_GET['username'])) {
		if (!preg_match('/\\A(?:[[:alnum:]][[:alnum:]\\-]*\\.?)*[[:alnum:]]\\z/i', $_GET['username'])) {
			throw new Exception('Username is not valid!');
		}
		$creds[$_GET['uuid']]['username'] = $_GET['username'];
		$dirty = TRUE;
	}

	if (isset($_GET['password'])) {
		if (!preg_match('/\\A\\w{8,}\\z/i', $_GET['password'])) {
			throw new Exception('Password is not valid!');
		}
		$creds[$_GET['uuid']]['password'] = $_GET['password'];
		$dirty = TRUE;
	}
	if ($dirty)
	{
		$creds[$_GET['uuid']]['modified'] = time();
	}

	$api->setCredentials($creds);
	
}
catch(Exception $e) {
    echo json_encode(array(
        'error' => "Exception: {$e->getMessage() }"
    ));
    exit;
}
echo json_encode($creds[$_GET['uuid']]);
?>
