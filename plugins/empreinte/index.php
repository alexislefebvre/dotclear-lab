<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Empreinte', a plugin for Dotclear 2               *
 *                                                             *
 *  Copyright (c) 2007,2008,2011                               *
 *  Alex Pirine and contributors.                              *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Empreinte' (see COPYING.txt);          *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

if (!defined('DC_CONTEXT_ADMIN')) { return; }

try
{
	$messages = array();
	$default_tab = empty($_GET['tab']) ? 'config' : html::escapeHTML($_GET['tab']);
	
	/* Initialisation
	--------------------------------------------------- */

	$authorlink_mask = $core->blog->settings->empreinte->authorlink_mask;
	$allow_disable = $core->blog->settings->empreinte->allow_disable;
	
	/* Réception des données depuis les formulaires
	--------------------------------------------------- */
	
	if (isset($_POST['action_config']))
	{
		$authorlink_mask = $_POST['authorlink_mask'];
		$allow_disable = !empty($_POST['allow_disable']);
	}
	
	/* Traitement des requêtes
	--------------------------------------------------- */
	
	if (isset($_POST['action_config']))
	{
		$core->blog->settings->empreinte->put('authorlink_mask',$authorlink_mask);
		$core->blog->settings->empreinte->put('allow_disable',$allow_disable);
		$core->blog->triggerBlog();
		$messages[] = __('Settings have been successfully updated.');
	}
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
}

/* DISPLAY
--------------------------------------------------- */

// Headers
echo '
<html><head>
<title>'.__('Empreinte').'</title>'.
dcPage::jsToolMan().($default_tab ? dcPage::jsPageTabs($default_tab) : '').'
</head><body>
<h2 style="padding:8px 0 8px 34px;background:url(index.php?pf=empreinte/icon_32.png) no-repeat;">'.
	__('Empreinte configuration').'</h2>';

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

// Forms
include dirname(__FILE__).'/forms.php';

echo
	'<div class="multi-part" id="config" title="'.__('Configuration').'">'.
	$_forms['admin_cfg'].
	"</div>\n\n".
	'<div class="multi-part" id="help" title="'.__('Help').'">'.
	$_forms['admin_help'].
	"</div>\n\n".
	'</body></html>';
?>