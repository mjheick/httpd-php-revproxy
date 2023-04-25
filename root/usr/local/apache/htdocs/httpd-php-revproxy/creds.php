<?php
require "lib/autoload.php";
$config = new RevProxy_Config(CONFIG_ROOT);
if (($message = RevProxy_Login::ensureAuthenticated($config)) !== true) {
    echo $message;
    exit;
}


$api = new RevProxy_API($config);
	
$creds = $api->getCredentials();

uksort($creds, 'strnatcasecmp');
?>
    <h3>All credentials will be disabled unless modified within <?php echo htmlentities($config->get('credential_expiry_time')); ?> hours.</h3>
    <h3>When modifying credentials change the password to something not guessable based upon the previous password.</h3>
    <button class="create_new" onclick="create_new_credential();" href="#">Create New Credential</button>

	<div class="accordion" id="accordion_creds">
		<?php
	foreach ($creds as $cred_uuid => $cred)
	{
?>
        <h3 id="h3_<?php echo htmlspecialchars(str_replace('.', '_', $cred['username']), ENT_QUOTES, 'UTF-8'); ?>"><?php if (!$cred['enabled']) { ?><div class="disabled">DISABLED</div><?php } ?><a href="#">Username: <b class="server-name"><?php echo htmlspecialchars($cred['username'], ENT_NOQUOTES, 'UTF-8'); ?></b><br />Password: <b class="server-name"><?php echo htmlspecialchars($cred['password'], ENT_NOQUOTES, 'UTF-8'); ?></b></a></h3>
        <div id="div_<?php echo htmlspecialchars(str_replace('.', '_', $cred['username']), ENT_QUOTES, 'UTF-8'); ?>">
        
        Created: <span class="ip"><?php echo htmlspecialchars(strftime('%a %d %b %H:%M:%S %Y', $cred['created']), ENT_NOQUOTES, 'UTF-8'); ?></span><br />
        Last Modified: <span class="ip"><?php echo htmlspecialchars(strftime('%a %d %b %H:%M:%S %Y', $cred['modified']), ENT_NOQUOTES, 'UTF-8'); ?></span><br />

        <ul class="actions">
        	<li><a href="#" onclick="run_task('<?php echo htmlspecialchars($cred_uuid, ENT_QUOTES, 'UTF-8'); ?>', 'api/cred.php?uuid=<?php echo htmlspecialchars(urlencode($cred_uuid), ENT_QUOTES, 'UTF-8'); ?>&amp;enabled=<?php echo htmlspecialchars($cred['enabled'] === TRUE ? 'false' : 'true', ENT_NOQUOTES, 'UTF-8'); ?>');"><span class="icon ui-icon-power"/> <?php echo htmlspecialchars($cred['enabled'] === TRUE ? 'Disable' : 'Enable', ENT_NOQUOTES, 'UTF-8'); ?></a></li>
        	<li><a href="#" onclick="modify_cred_data('<?php echo htmlspecialchars($cred_uuid, ENT_QUOTES, 'UTF-8'); ?>');"><span class="icon ui-icon-newwin"/> Modify</a></li>
        	<li><a href="#" onclick="if (!confirm('Are you sure you want to delete this credential pair?')) { return false; }run_task('<?php echo htmlspecialchars($cred_uuid, ENT_QUOTES, 'UTF-8'); ?>', 'api/delete_cred.php?uuid=<?php echo htmlspecialchars(urlencode($cred_uuid), ENT_QUOTES, 'UTF-8'); ?>');"><span class="icon ui-icon-trash"/> Delete</a></li>
        </ul>
		</div>

<?php
    }
?>

	</div>
	<script language="javascript" type="text/javascript">
	$('#accordion_creds').accordion({autoHeight: false, collapsible: true, active: false});
	$('button').button({icons: {primary: "ui-icon-gear"}});
	</script>
