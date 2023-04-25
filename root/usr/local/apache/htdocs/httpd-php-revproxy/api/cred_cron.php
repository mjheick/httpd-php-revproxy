<?php

require "../lib/autoload.php";
$config = new RevProxy_Config(CONFIG_ROOT);

$classes = array();

try {
	$api = new RevProxy_API($config);
	$creds = $api->getCredentials();

	$dirty = FALSE;

	$expiry_time = time() - (60 * 60 * $config->get('credential_expiry_time'));
	foreach ($creds as $uuid => $cred)
	{
		if ($creds[$uuid]['modified'] < $expiry_time)
		{
			$dirty = true;
			$creds[$uuid]['enabled'] = false;
		}
	}

	if ($dirty)
	{
		$api->setCredentials($creds);
	}
	
}
catch(Exception $e) {
    echo json_encode(array(
        'error' => "Exception: {$e->getMessage() }"
    ));
    exit;
}
?>
