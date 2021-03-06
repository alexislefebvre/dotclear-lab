<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Jabber Notifications, a plugin for Dotclear.
# 
# Copyright (c) 2007,2008,2011 Alex Pirine <alex pirine.fr>, Olivier Tétard
# 
# Licensed under the GPL version 2.0 license.
# A copy is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

dcPage::checkSuper();

try
{
	$errors = $messages = array();
	$default_tab = 'config_tab';
	
	/* Initialisation
	--------------------------------------------------- */
	
	$s = &$core->blog->settings->jabbernotifications;

	// Vérification de l'installation
	if ($s->jn_enab === null || isset($_GET['reset']))
	{
		$s->put('jn_serv','','string','Host',true,true);
		$s->put('jn_port',5222,'integer','Port',true,true);
		$s->put('jn_con','','string','Secure connection protocol',true,true);
		$s->put('jn_user','','string','Username used to connect to the jabber server',true,true);
		$s->put('jn_pass','','string','Password used to connect to the jabber server',true,true);
		$s->put('jn_enab',false,'boolean','Enable',true,true);
		$s->put('jn_gateway','','string','HTTP Gateway',true,true);
		http::redirect($p_url.'&init=1');
	}

	// Récupération des paramètres
	else
	{
		$jn_enab = $s->jn_enab;
		$jn_user = $s->jn_user;
		$jn_pass = @base64_decode($s->jn_pass);
		$jn_serv = $s->jn_serv;
		$jn_port = $s->jn_port;
		$jn_con = $s->jn_con;
		$jn_gateway = $s->jn_gateway;
		$jn_dest = '';
	}

	/* Réception des données depuis les formulaires
	--------------------------------------------------- */

	if (isset($_POST['jn_action_config']) || isset($_POST['jn_action_test']))
	{
		if (empty($_POST['jn_enab']))
		{
			$jn_enab = false;
		}
		else
		{
			$jn_enab = true;
			$jn_user = $_POST['jn_user'];
			$jn_pass = $_POST['jn_pass'];
			$jn_serv = $_POST['jn_serv'];
			$jn_port = $_POST['jn_port'];
			$jn_con = $_POST['jn_con'];
			$jn_gateway = $_POST['jn_gateway'];
			$jn_dest = $_POST['jn_dest'];
		}
	}
	
	/* Traitement des requêtes
	--------------------------------------------------- */
	
	if (isset($_POST['jn_action_config'])) {
		if (!$jn_enab) {
			$s->put('jn_enab',false,null,null,true,true);
			$messages[] = __('Jabber notifications have been disabled.');
		}
		else {
			if (empty($jn_user)) {
				$errors[] = __('Username was not set');
			}
			if (empty($jn_pass)) {
				$errors[] = __('Password was not set');
			}
			if (empty($jn_serv)) {
				$errors[] = __('Server was not set');
			}
			if (empty($jn_port)) {
				$errors[] = sprintf(__('Port was not set (default is %d)'),5222);
			}
			if (!ereg('^[0-9]*$',$jn_port)) {
				$errors[] = sprintf(__('Port must be a number, e.g. %d'),5222);
			}
			
			if (empty($errors)) {
				$s->put('jn_user',$jn_user,null,null,true,true);
				$s->put('jn_pass',base64_encode($jn_pass),null,null,true,true);
				$s->put('jn_serv',$jn_serv,null,null,true,true);
				$s->put('jn_port',$jn_port,null,null,true,true);
				$s->put('jn_con',$jn_con,null,null,true,true);
				$s->put('jn_enab',true,null,null,true,true);
				$s->put('jn_gateway',$jn_gateway,'string','HTTP Gateway',true);
				$messages[] = __('Settings have been successfully updated.');
			}
			else {
				throw new Exception();
			}
		}
	}
	elseif (isset($_POST['jn_action_test'])) {
		$time = microtime(true);
		$j = new jabberNotifier($jn_serv,$jn_port,$jn_user,$jn_pass,$jn_con);
		$j->setMessage('Jabber Notifications test. UNIX timestamp is '.time().' s.');
		$j->addDestination(explode(',',$jn_dest));
		if (empty($jn_gateway)) {
			$j->commit();
		} else {
			$j->commitThroughGateway($jn_gateway);
		}
		$time = microtime(true)-$time;
		
		$statuses = array(
			'ok'=>'Message succefully sended.',
			'notDone'=>'I did not finished. It can mean connection timeout.',
			'gatewayConnectFailure'=>'Connection to the gateway failed.',
			'connectFailure'=>'Connection to the jabber server failed.',
			'loginFailure'=>'Login failure.',
			'authFailure'=>'Authentification failure.',
			'messageFailure'=>'Unable to send message.',
			'streamError'=>'A stream error occured during connection.');
		$status = isset($statuses[$j->status])
			? $statuses[$j->status]
			: __('Unknown status:').' '.html::escapeHTML($j->status);
		$messages[] = sprintf(__('Test result (in %.3f s):'),$time).' '.__($status);
	}
}

/* Gestion des erreurs et des messages
--------------------------------------------------- */

catch (Exception $e)
{
	$error_str = $e->getMessage() ? $e->getMessage() :
	__('The following fields contain errors :')."\n".
	'<ul><li>'.
	implode("</li>\n<li>",(!empty($errors) ? $errors :
		array(sprintf(__('Unexpected error at line %d in %s'),getLine(),getFile())))).
	'</li></ul>';

	$core->error->add($error_str);
}

if (!empty($_GET['init']))
{
	$messages[] = __('Settings have been initialized.');
}

/* DISPLAY
--------------------------------------------------- */

// Headers

echo
'<html><head>
<title>'.__('Jabber notifications').'</title>
'.dcPage::jsToolMan().
($default_tab ? dcPage::jsPageTabs($default_tab) : '').
'<script type="text/javascript">
//<![CDATA[
$(function() {
	$("#jn_enab").change(function()
	{
		if (this.checked)
			$("#jn_config").show();
		else
			$("#jn_config").hide();
	});
	
	if (!document.getElementById(\'jn_enab\').checked) {
		$("#jn_config").hide();
	}
});
//]]>
</script>
</head><body>
<h2>'.__('Jabber notifications').'</h2>';

// Messages
if (!empty($messages))
{
	if (count($messages) < 2)
	{
		echo '<p class="message">'.end($messages)."</p>\n";
	}
	else
	{
		echo '<ul class="message">';
		foreach ($messages as $message)
		{
			echo '<li>'.$message.'</li>';
		}
		echo "</ul>\n";
	}
}

include dirname(__FILE__).'/forms.php';

echo
'<div class="multi-part" id="config_tab" title="'.__('Configuration').'">'.
	$jn_forms['config'].'</div>'.
'<div class="multi-part" id="help_tab" title="'.__('Help').'">'.
	$jn_forms['help'].'</div>';
?>
</body></html>