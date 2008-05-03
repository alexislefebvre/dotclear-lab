<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Remote LaTeX', a plugin for Dotclear              *
 *                                                             *
 *  Copyright (c) 2007-2008                                    *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Remote LaTeX' (see COPYING.txt);       *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

try
{
	include dirname(__FILE__).'/models.php';

	$messages = array();
	$default_tab = empty($_GET['tab']) ? 'config' : html::escapeHTML($_GET['tab']);
	
	/* Initialization
	--------------------------------------------------- */
	
	remoteLatex::getSettings($latex_server,$root_path,$root_url);
	
	/* Saving data from the forms
	--------------------------------------------------- */
	
	if (isset($_POST['action_config'])) {
		$latex_server = $_POST['latex_server'];
	}
	
	/* Query processing
	--------------------------------------------------- */
	
	if (isset($_POST['action_config'])) {
		if ($latex_server == '') {
			throw new Exception(__('LaTeX server location is empty'));
		}
		$core->blog->settings->setNamespace('latex');
		$core->blog->settings->put('latex_server',$latex_server);
		$messages[] = __('Settings have been successfully updated.');
	}
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
}

/* DISPLAY
--------------------------------------------------- */

echo '
<html><head>
<title>'.__('Remote LaTeX').'</title>'.
dcPage::jsToolMan().($default_tab ? dcPage::jsPageTabs($default_tab) : '').'
</head><body>
<h2>'.__('Remote LaTeX configuration').'</h2>';

// Messages
if (!empty($messages)) {
	if (count($messages) < 2) {
		echo '	<p class="message">'.end($messages)."</p>\n";
	}
	else {
		echo '<ul class="message">';
		foreach ($messages as $message)
		{
			echo '	<li>'.$message."</li>\n";
		}
		echo "</ul>\n";
	}
}

include dirname(__FILE__).'/forms.php';

echo '<div class="multi-part" id="config" title="'.__('Configuration').'">'.
	$forms['admin_cfg'].
	"</div>";
?>
</body></html>