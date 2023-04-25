<?php
require "lib/autoload.php";
$config = new RevProxy_Config(CONFIG_ROOT);
if (($message = RevProxy_Login::ensureAuthenticated($config)) !== true) {
    echo $message;
    exit;
}


$api = new RevProxy_API($config);
	
$redirects = $api->getRedirects();

uksort($redirects, 'strnatcasecmp');
?>
    <button class="create_new" onclick="create_new();" href="#">Create New Redirect</button>

	<div class="accordion" id="accordion_redirect">
		<?php
	foreach ($redirects as $redirect_uuid => $redirect)
	{
?>
        <h3 id="h3_<?php echo htmlspecialchars(str_replace('.', '_', $redirect['source']), ENT_QUOTES, 'UTF-8'); ?>"><?php if (!$redirect['enabled']) { ?><div class="disabled">DISABLED</div><?php } ?><a href="#">Incoming: <b class="server-name">http://<?php echo htmlspecialchars($redirect['source'], ENT_NOQUOTES, 'UTF-8'); ?>/</b><br />Route To: <b class="server-name">http://<?php echo htmlspecialchars($redirect['destination'], ENT_NOQUOTES, 'UTF-8'); ?>/</b></a></h3>
        <div id="div_<?php echo htmlspecialchars(str_replace('.', '_', $redirect['source']), ENT_QUOTES, 'UTF-8'); ?>">
        
        Created: <span class="ip"><?php echo htmlspecialchars(strftime('%a %d %b %H:%M:%S %Y', $redirect['created']), ENT_NOQUOTES, 'UTF-8'); ?></span><br />
        Last Modified: <span class="ip"><?php echo htmlspecialchars(strftime('%a %d %b %H:%M:%S %Y', $redirect['modified']), ENT_NOQUOTES, 'UTF-8'); ?></span><br />

        <ul class="actions">
        	<li><a href="#" onclick="run_task('<?php echo htmlspecialchars($redirect_uuid, ENT_QUOTES, 'UTF-8'); ?>', 'api/redirect.php?uuid=<?php echo htmlspecialchars(urlencode($redirect_uuid), ENT_QUOTES, 'UTF-8'); ?>&amp;enabled=<?php echo htmlspecialchars($redirect['enabled'] === TRUE ? 'false' : 'true', ENT_NOQUOTES, 'UTF-8'); ?>');"><span class="icon ui-icon-power"/> <?php echo htmlspecialchars($redirect['enabled'] === TRUE ? 'Disable' : 'Enable', ENT_NOQUOTES, 'UTF-8'); ?></a></li>
        	<li><a href="#" onclick="modify_proxy_data('<?php echo htmlspecialchars($redirect_uuid, ENT_QUOTES, 'UTF-8'); ?>');"><span class="icon ui-icon-newwin"/> Modify</a></li>
        	<li><a href="#" onclick="if (!confirm('Are you sure you want to delete this redirection?')) { return false; }run_task('<?php echo htmlspecialchars($redirect_uuid, ENT_QUOTES, 'UTF-8'); ?>', 'api/delete.php?uuid=<?php echo htmlspecialchars(urlencode($redirect_uuid), ENT_QUOTES, 'UTF-8'); ?>');"><span class="icon ui-icon-trash"/> Delete</a></li>
        </ul>
		</div>

<?php
    }
?>

	</div>
	<script language="javascript" type="text/javascript">
	$('#accordion_redirect').accordion({autoHeight: false, collapsible: true, active: false});
	$('button').button({icons: {primary: "ui-icon-gear"}});
	</script>
