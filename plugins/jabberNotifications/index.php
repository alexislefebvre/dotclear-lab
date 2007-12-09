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
	}

	/* Réception des données depuis les formulaires
	--------------------------------------------------- */

	if (isset($_POST['jn_action_config']))
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
		}
	}
	
	/* Traitement des requêtes
	--------------------------------------------------- */
	
	if (isset($_POST['jn_action_config']))
	{
		if (!$jn_enab)
		{
			$core->blog->settings->put('jn_enab',false,null,null,true,true);
			$messages[] = __('Jabber notifications have been disabled.');
		}
		else
		{
			if (empty($jn_user))
				$errors[] = __('Username was not set');
			if (empty($jn_pass))
				$errors[] = __('Password was not set');
			if (empty($jn_serv))
				$errors[] = __('Server was not set');
			if (empty($jn_port))
				$errors[] = sprintf(__('Port was not set (default is %d)'),5222);
			if (!ereg('^[0-9]*$',$jn_port))
				$errors[] = sprintf(__('Port must be a number, e.g. %d'),5222);
			
			if (empty($errors))
			{
				$core->blog->settings->put('jn_user',$jn_user,null,null,true,true);
				$core->blog->settings->put('jn_pass',$jn_pass,null,null,true,true);
				$core->blog->settings->put('jn_serv',$jn_serv,null,null,true,true);
				$core->blog->settings->put('jn_port',$jn_port,null,null,true,true);
				$core->blog->settings->put('jn_enab',true,null,null,true,true);
				$messages[] = __('Settings have been successfully updated.');
			}
			else
			{
				throw new Exception('');
			}
		}
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
?>

<html><head>
<title><?php echo __('Jabber notifications'); ?></title>
<script type="text/javascript">
//<![CDATA[
$(function() {
	$("#jn_enab").change(function()
	{
		if (this.checked)
			$("#jn_config").show();
		else
			$("#jn_config").hide();
	});
	
	if (!document.getElementById('jn_enab').checked)
		$("#jn_config").hide();
});
//]]>
</script>
</head><body>
<?php
echo '<h2>'.__('Jabber notifications').'</h2>';

// Messages
if (!empty($messages))
{
	if (count($messages) < 2)
	{
		echo '	<p class="message">'.end($messages)."</p>\n";
	}
	else
	{
		echo '<ul class="message">';
		foreach ($messages as $message)
		{
			echo '	<li>'.$message."</li>\n";
		}
		echo "</ul>\n";
	}
}

include dirname(__FILE__).'/forms.php';
echo $jn_forms['config'];
?>
</body></html>
