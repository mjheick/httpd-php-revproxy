<?php
require "lib/autoload.php";
$config = new RevProxy_Config(CONFIG_ROOT);
if (($message = RevProxy_Login::ensureAuthenticated($config)) !== true) {
    echo $message;
    exit;
}


$api = new RevProxy_API($config);

$permanent_ips = $api->getPermanentIps();

$ips = $api->getIps();

$ips = RevProxy_API::sortByOneKey($ips, 'name');

?>
    <button class="create_new" onclick="add_ip();" href="#">Create IP Allowance</button>

	<div class="accordion" id="accordion_ip">

	<h3 id="h3_permanent"><div class="permanent">PERMANENT</div><a href="#"><b class="server-name">Permanent</b></a></h3>
	<div id="div_permanent">
	IPs:<br />
	<ul>
<?php
	foreach ($permanent_ips as $ip)
	{
?>
		<li><?php echo htmlspecialchars($ip, ENT_NOQUOTES, 'UTF-8'); ?></li>
<?php
	}
?>
	</ul>
	</div>
<?php
	foreach ($ips as $ip_uuid => $ip)
	{
?>
        <h3 id="h3_<?php echo htmlspecialchars($ip_uuid, ENT_QUOTES, 'UTF-8'); ?>"><?php if (!$ip['enabled']) { ?><div class="disabled">DISABLED</div><?php } ?><a href="#"><b class="server-name"><?php echo htmlspecialchars($ip['name'], ENT_NOQUOTES, 'UTF-8'); ?></b></a></h3>
        <div id="div_<?php echo htmlspecialchars($ip_uuid, ENT_QUOTES, 'UTF-8'); ?>">
        Created: <span class="ip"><?php echo htmlspecialchars(strftime('%a %d %b %H:%M:%S %Y', $ip['created']), ENT_NOQUOTES, 'UTF-8'); ?></span><br />
        Last Modified: <span class="ip"><?php echo htmlspecialchars(strftime('%a %d %b %H:%M:%S %Y', $ip['modified']), ENT_NOQUOTES, 'UTF-8'); ?></span><br />

		IPs:<br />
		<ul>
<?php
	foreach ($ip['addresses'] as $ip_value)
	{
?>
		<li><?php echo htmlspecialchars($ip_value, ENT_NOQUOTES, 'UTF-8'); ?></li>
<?php
	}
?>	
		</ul>
        
        <ul class="actions">
        	<li><a href="#" onclick="run_task('<?php echo htmlspecialchars($ip_uuid, ENT_QUOTES, 'UTF-8'); ?>', 'api/ip.php?uuid=<?php echo htmlspecialchars(urlencode($ip_uuid), ENT_QUOTES, 'UTF-8'); ?>&amp;enabled=<?php echo htmlspecialchars($ip['enabled'] === TRUE ? 'false' : 'true', ENT_NOQUOTES, 'UTF-8'); ?>');"><span class="icon ui-icon-power"/> <?php echo htmlspecialchars($ip['enabled'] === TRUE ? 'Disable' : 'Enable', ENT_NOQUOTES, 'UTF-8'); ?></a></li>
        	<li><a href="#" onclick="modify_ip_data('<?php echo htmlspecialchars($ip_uuid, ENT_QUOTES, 'UTF-8'); ?>');"><span class="icon ui-icon-newwin"/> Modify</a></li>
        	<li><a href="#" onclick="if (!confirm('Are you sure you want to delete this ip?')) { return false; }run_task('<?php echo htmlspecialchars($ip_uuid, ENT_QUOTES, 'UTF-8'); ?>', 'api/delete_ip.php?uuid=<?php echo htmlspecialchars(urlencode($ip_uuid), ENT_QUOTES, 'UTF-8'); ?>');"><span class="icon ui-icon-trash"/> Delete</a></li>
        </ul>
		</div>

<?php
    }
?>

	</div>
	<script language="javascript" type="text/javascript">
	$('#accordion_ip').accordion({autoHeight: false, collapsible: true, active: false});
	$('button').button({icons: {primary: "ui-icon-gear"}});
	</script>
