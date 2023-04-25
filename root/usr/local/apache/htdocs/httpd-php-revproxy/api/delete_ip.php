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

	unset($ips[$_GET['uuid']]);

	$api->setIps($ips);
	
}
catch(Exception $e) {
    echo json_encode(array(
        'error' => "Exception: {$e->getMessage() }"
    ));
    exit;
}
echo json_encode($redirects);
?>
