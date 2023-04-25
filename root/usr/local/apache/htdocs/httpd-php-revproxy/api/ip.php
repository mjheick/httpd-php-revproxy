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
	$ips = $api->getIps();

	if (!isset($ips[$_GET['uuid']]))
	{
		throw new Exception("Invalid uuid");
	}

	$dirty = FALSE;
	if (isset($_GET['enabled'])) {
		if (!preg_match('/\\A(?:true|false)\\z/i', $_GET['enabled'])) {
			throw new Exception('Enabled is not valid!');
		}
		$ips[$_GET['uuid']]['enabled'] = ($_GET['enabled'] === 'true');
		$dirty = TRUE;
	}

	if (isset($_GET['addresses'])) {
		$addresses = explode(',', $_GET['addresses']);
		foreach	($addresses as $address) {
			if (!preg_match('{\A(?:(?:25[0-5]|2[0-4][[:digit:]]|[01]?[[:digit:]][[:digit:]]?)\.){3}(?:25[0-5]|2[0-4][[:digit:]]|[01]?[[:digit:]][[:digit:]]?)(?:/(?:[[:digit:]]|[0-3][[:digit:]]))?\z}i', $address)) {
				throw new Exception('Address must be a valid IPv4 CIDR address!');
			}
		}
		$ips[$_GET['uuid']]['addresses'] = $addresses;
		$dirty = TRUE;
	}

	if (isset($_GET['name'])) {
		if (!ctype_print($_GET['name'])) {
			throw new Exception('Invalid name');
		}
		$ips[$_GET['uuid']]['name'] = $_GET['name'];
		$dirty = TRUE;
	}
	if ($dirty)
	{
		$ips[$_GET['uuid']]['modified'] = time();
	}	

	$api->setIps($ips);
	
}
catch(Exception $e) {
    echo json_encode(array(
        'error' => "Exception: {$e->getMessage() }"
    ));
    exit;
}
echo json_encode($ips[$_GET['uuid']]);
?>
