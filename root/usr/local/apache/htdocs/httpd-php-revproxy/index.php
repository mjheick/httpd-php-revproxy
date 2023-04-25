<?php

require "lib/autoload.php";
$config = new RevProxy_Config(CONFIG_ROOT);
if (($message = RevProxy_Login::ensureAuthenticated($config)) !== true) {
	echo $message;
	exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en" xml:lang="en">
	<head>
		<title>Virtualization Provisioning Platform</title>
		<link rel="stylesheet" type="text/css" href="css/style.css" />
		<link rel="stylesheet" type="text/css" href="css/pepper-grinder/jquery-ui-1.8.9.custom.css" />
		<script language="javascript" type="text/javascript" src="js/jquery-1.4.4.min.js"></script>
		<script language="javascript" type="text/javascript" src="js/jquery-ui-1.8.9.custom.min.js"></script>
		<script language="javascript" type="text/javascript" src="js/jquery.jmpopups-0.5.1.js"></script>
		<script language="javascript" type="text/javascript" src="js/redirect.js"></script>
		<script language="javascript" type="text/javascript">
			$(function() {
				$( "#tabs" ).tabs({
					cache: false,
					ajaxOptions: {
						error: function( xhr, status, index, anchor ) {
							$( anchor.hash ).html('Could not load server information.  Please check the error logs.');
						}
					}
				});
			});
	</script>
	</head>
	<body>
		<div class="header">
			<span class="logo">Logo</span>
			<span>Easy Proxy</span>
			<span class="login">
				<a href="logout.php">Logout</a> | 
				<?php echo htmlspecialchars($_SESSION['authenticated']['user'], ENT_QUOTES, 'UTF-8'); ?>
			</span>
		</div>
		<div id="tabs">
			<ul>
				<li><a href="redirects.php"><span>Redirects</span></a></li>
				<li><a href="ips.php"><span>IPs</span></a></li>
				<li><a href="creds.php"><span>Credentials</span></a></li>
			</ul>
		</div>
		<noscript>Must be running JavaScript to work with this page.</noscript>
	</body>
</html>
