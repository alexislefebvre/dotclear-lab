<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */

/***************************************************************\
 *  This is Jabber Notifications, a plugin for Dotclear 2      *
 *                                                             *
 *  Copyright (c) 2007                                         *
 *  Oleksandr Syenchuk, Olivier Tétard and contributors.       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along Jabber Notifications (see COPYING.txt);      *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

dcPage::checkSuper();

try
{
	$errors = $messages = array();
	$default_tab = 'config_tab';
	
	/* Initialisation
	--------------------------------------------------- */

	$core->blog->settings->setNameSpace('jabbernotifications');

	// Vérification de l'installation
	if ($core->blog->settings->jn_enab === null)
	{
		$core->blog->settings->put('jn_serv','','string','Server',true,true);
		$core->blog->settings->put('jn_port',5222,'integer','Port',true,true);
		$core->blog->settings->put('jn_user','','string','Username used to connect to the jabber server',true,true);
		$core->blog->settings->put('jn_pass','','string','Password used to connect to the jabber server',true,true);
		$core->blog->settings->put('jn_enab',false,'boolean','Enable',true,true);
		$core->blog->settings->put('jn_gateway','','string','HTTP Gateway',true,true);
		http::redirect($p_url.'&init=1');
	}

	// Récupération des paramètres
	else
	{
		$jn_enab = $core->blog->settings->jn_enab;
		$jn_user = $core->blog->settings->jn_user;
		$jn_pass = $core->blog->settings->jn_pass;
		$jn_serv = $core->blog->settings->jn_serv;
		$jn_port = $core->blog->settings->jn_port;
		$jn_gateway = $core->blog->settings->jn_gateway;
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
			$jn_gateway = $_POST['jn_gateway'];
			$jn_dest = $_POST['jn_dest'];
		}
	}
	
	/* Traitement des requêtes
	--------------------------------------------------- */
	
	if (isset($_POST['jn_action_config'])) {
		if (!$jn_enab) {
			$core->blog->settings->put('jn_enab',false,null,null,true,true);
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
				$core->blog->settings->put('jn_user',$jn_user,null,null,true,true);
				$core->blog->settings->put('jn_pass',$jn_pass,null,null,true,true);
				$core->blog->settings->put('jn_serv',$jn_serv,null,null,true,true);
				$core->blog->settings->put('jn_port',$jn_port,null,null,true,true);
				$core->blog->settings->put('jn_enab',true,null,null,true,true);
				$core->blog->settings->put('jn_gateway',$jn_gateway,'string','HTTP Gateway',true);
				$messages[] = __('Settings have been successfully updated.');
			}
			else {
				throw new Exception();
			}
		}
	}
	elseif (isset($_POST['jn_action_test'])) {
		$j = new jabberNotifier($jn_serv,$jn_port,$jn_user,$jn_pass);
		$j->setMessage('Jabber Notifications test. UNIX timestamp is '.time().' s.');
		$j->addDestination(explode(',',$jn_dest));
		if (empty($jn_gateway)) {
			$j->commit();
		} else {
			$j->commitThroughGateway($jn_gateway);
		}
		
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
		$messages[] = __('Test result:').' '.__($status);
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
