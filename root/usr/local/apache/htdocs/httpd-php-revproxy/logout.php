<?php
	session_start();
	$_SESSION['authenticated'] = false;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en" xml:lang="en">
	<head>
		<title>Virtualization Provisioning Platform</title>
		<link rel="stylesheet" type="text/css" href="css/style.css" />
	</head>
	<body>
		<div class="header">
			<span class="logo">Logo</span>
			<span>Virtual Provisioning</span>
		</div>
		<div>
			<p>You have been logged out.  <a href="index.php">Go back to the main page to log in again</a>.</p>
		</div>
	</body>
</html>
