function run_task(name, url) {
	$.ajax({ url: url,
		dataType: 'json',
		success: function() {
			refresh_me();
		},
		error: function() {
			alert("Failed to run task.");
		},
	});
}

function create_new() {

	var html = '';

	html += '<div id="basic_div">';
	html += '<b class="header">Incoming</b><hr />'; 
	html += '<table class="options">';
	html += '<tr><td class="key"><label for="redirect_source"><b>Hostname: </b></label></td><td><input type="text" id="redirect_source" value="" /></td></tr>';
	html += '</table>';
	html += '<b class="header">Route To</b><hr />'; 
	html += '<table class="options">';
	html += '<tr><td class="key"><label for="redirect_destination"><b>Hostname/IP: </b></label></td><td><input type="text" id="redirect_destination" value="" /></td></tr>';
	html += '</table>';
	html += '</div>';
	html += '<input id="redirect_submit" onclick="redirect_submit();" style="float:right" type="submit" value="Create Redirect" /><br />';

	popup('Create Redirect', html, {});
	refresh_me();
}

function create_new_credential() {

	var html = '';

	html += '<div id="basic_div">';
	html += '<b class="header">Credentials</b><hr />'; 
	html += '<table class="options">';
	html += '<tr><td class="key"><label for="cred_username"><b>Username: </b></label></td><td><input type="text" id="cred_username" value="" /></td></tr>';
	html += '</table>';
	html += '<table class="options">';
	html += '<tr><td class="key"><label for="cred_password"><b>Password: </b></label></td><td><input type="text" id="cred_password" value="" /></td></tr>';
	html += '</table>';
	html += '</div>';
	html += '<input id="cred_submit" onclick="cred_submit();" style="float:right" type="submit" value="Create Credential" /><br />';

	popup('Create Credential', html, {});
	refresh_me();
}

function add_ip() {

	var html = '';

	html += '<div id="basic_div">';
	html += '<b class="header">Settings</b><hr />'; 
	html += '<table class="options">';
	html += '<tr><td class="key"><label for="ip_name"><b>Name: </b></label></td><td><input type="text" id="ip_name" value="" /></td></tr>';
	html += '<tr><td class="key"><label for="ip_addresses"><b>IP List (comma-separated CIDR blocks): </b></label></td><td><input type="text" id="ip_addresses" value="" /></td></tr>';
	html += '</table>';
	html += '</div>';
	html += '<input id="ip_submit" onclick="ip_submit();" style="float:right" type="submit" value="Create IP Allowance" /><br />';

	popup('Create IP Allowance', html, {});
	refresh_me();
}

function modify_ip_data(uuid) {
	$.ajax({
		url: 'api/ip.php?uuid=' + encodeURIComponent(uuid),
		dataType: 'json',
		success: function(data) {
			var html = '';

			if (data == null || data.error) {
				alert('Failed to retrieve ip information');
				return;
			}

			html += '<div id="basic_div">';
			html += '<b class="header">Settings</b><hr />'; 
			html += '<table class="options">';
			html += '<tr><td class="key"><label for="ip_name"><b>Name: </b></label></td><td><input type="text" id="ip_name" value="'+htmlEscape(data.name)+'" /></td></tr>';
			html += '<tr><td class="key"><label for="ip_addresses"><b>IP List (comma-separated CIDR blocks): </b></label></td><td><input type="text" id="ip_addresses" value="'+htmlEscape(data.addresses.join(','))+'" /></td></tr>';
			html += '</table>'; 
			html += '<input type="hidden" id="ip_uuid" value="' + htmlEscape(uuid) + '" />';
			html += '</div>';
			html += '<input id="ip_submit" onclick="ip_submit();" style="float:right" type="submit" value="Modify IP Allowance" /><br />';

			popup('Modify IP Allowance', html, {});
		},
		error: function() {
			alert('Failed to retrieve IP information');
		}});
}


function modify_proxy_data(uuid) {
	$.ajax({
		url: 'api/redirect.php?uuid=' + encodeURIComponent(uuid),
		dataType: 'json',
		success: function(data) {
			var html = '';

			if (data == null || data.error) {
				alert('Failed to retrieve redirect information');
				return;
			}

			html += '<div id="basic_div">';
			html += '<b class="header">Incoming</b><hr />'; 
			html += '<table class="options">';
			html += '<tr><td class="key"><label for="redirect_source"><b>Hostname: </b></label></td><td><input type="text" id="redirect_source" value="' + htmlEscape(data.source) + '" /></td></tr>';
			html += '</table>'; 
			html += '<b class="header">Route To</b><hr />'; 
			html += '<table class="options">';
			html += '<tr><td class="key"><label for="redirect_destination"><b>Hostname/IP: </b></label></td><td><input type="text" id="redirect_destination" value="' + htmlEscape(data.destination) + '" /></td></tr>';
			html += '</table>'; 
			html += '<input type="hidden" id="redirect_uuid" value="' + htmlEscape(uuid) + '" />';
			html += '</div>';
			html += '<input id="redirect_submit" onclick="redirect_submit();" style="float:right" type="submit" value="Modify Redirect" /><br />';

			popup('Modify Redirect', html, {});
		},
		error: function() {
			alert('Failed to retrieve redirect information');
		}});
}

function modify_cred_data(uuid) {
	$.ajax({
		url: 'api/cred.php?uuid=' + encodeURIComponent(uuid),
		dataType: 'json',
		success: function(data) {
			var html = '';

			if (data == null || data.error) {
				alert('Failed to retrieve credential information');
				return;
			}

			html += '<div id="basic_div">';
			html += '<b class="header">Credentials</b><hr />'; 
			html += '<table class="options">';
			html += '<tr><td class="key"><label for="cred_username"><b>Username: </b></label></td><td><input type="text" id="cred_username" value="' + htmlEscape(data.username) + '" /></td></tr>';
			html += '</table>'; 
			html += '<table class="options">';
			html += '<tr><td class="key"><label for="cred_password"><b>Password: </b></label></td><td><input type="text" id="cred_password" value="' + htmlEscape(data.password) + '" /></td></tr>';
			html += '</table>'; 
			html += '<input type="hidden" id="cred_uuid" value="' + htmlEscape(uuid) + '" />';
			html += '</div>';
			html += '<input id="cred_submit" onclick="cred_submit();" style="float:right" type="submit" value="Modify Credential" /><br />';

			popup('Modify Credential', html, {});
		},
		error: function() {
			alert('Failed to retrieve credential information');
		}});
}

function redirect_submit() {
	var url;
	var message;
	if ($('#redirect_uuid').val()) {
		message = 'update';
		url = 'api/redirect.php' +
		'?uuid=' + encodeURIComponent($('#redirect_uuid').val()) + '&';
	} else {
		message = 'create'
		url = 'api/create.php?';
	}
	url += 'source=' + encodeURIComponent($('#redirect_source').val()) +
		'&destination=' + encodeURIComponent($('#redirect_destination').val());

	$.ajax({ 
		url: url,
		dataType: 'json',
		success: function(data) {
			if (data.error) {
				alert(data.error);
			} else {
				alert('Redirect '+message+' successful.');
				$.closePopupLayer();
				refresh_me();	
			}
		},
		error: function() {
			alert('Failed to '+message+' the redirect');
		}});

}
function cred_submit() {
	var url;
	var message;
	if ($('#cred_uuid').val()) {
		message = 'update';
		url = 'api/cred.php' +
		'?uuid=' + encodeURIComponent($('#cred_uuid').val()) + '&';
	} else {
		message = 'create'
		url = 'api/create_cred.php?';
	}
	url += 'username=' + encodeURIComponent($('#cred_username').val()) +
		'&password=' + encodeURIComponent($('#cred_password').val());

	$.ajax({ 
		url: url,
		dataType: 'json',
		success: function(data) {
			if (data.error) {
				alert(data.error);
			} else {
				alert('Credential '+message+' successful.');
				$.closePopupLayer();
				refresh_me();	
			}
		},
		error: function() {
			alert('Failed to '+message+' the credential');
		}});
}

function ip_submit() {
	var url;
	var message;
	if ($('#ip_uuid').val()) {
		message = 'update';
		url = 'api/ip.php' +
		'?uuid=' + encodeURIComponent($('#ip_uuid').val()) + '&';
	} else {
		message = 'create'
		url = 'api/create_ip.php?';
	}
	url += 'name=' + encodeURIComponent($('#ip_name').val()) +
		'&addresses=' + encodeURIComponent($('#ip_addresses').val());

	$.ajax({ 
		url: url,
		dataType: 'json',
		success: function(data) {
			if (data.error) {
				alert(data.error);
			} else {
				alert('IP allowance '+message+' successful.');
				$.closePopupLayer();
				refresh_me();	
			}
		},
		error: function() {
			alert('Failed to '+message+' the IP allowance');
		}});
}

function refresh_me() {
	$("#tabs").tabs('load', $("#tabs").tabs('option', 'selected'));

}

var POPUP_COUNT = 0;
function popup(title, contents, clickEvents) {
	$.closePopupLayer('layer_popup' + POPUP_COUNT);
	var id = "popup" + ++POPUP_COUNT;
	var $popup;
	var $popup_child;

	$popup = $('<div style="display: none" id="' + id + '" />');
	$popup_child = $('<div id="' + id + '_child" class="popup">' +
			'<div class="popup-header">' +
				'<h2>' + title + '</h2>' +
				'<a href="javascript:;" onclick="$.closePopupLayer(\'layer_' + id + '\')" title="Close" class="close-link">Close</a>' +
				'<br clear="both" />' +
			'</div>' +
		'<div class="popup-body">' + contents + '</div></div>');

	$popup.append($popup_child);
	$('html').append($popup);
	$.openPopupLayer({
		name: 'layer_' + id,
		width: 600,
		target: id + '_child',
	});
	$popup.remove();
}

function htmlEscape(data)
{
	return $('<div/>').text(data).html();
}
