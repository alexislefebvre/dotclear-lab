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
if (!defined('DC_CONTEXT_ADMIN')) { return; }

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
	
	if (!empty($_POST)) {
		$latex_server = $_POST['latex_server'];
	}
	
	/* Query processing
	--------------------------------------------------- */
	
	if (!empty($_POST)) {
		if ($latex_server == '') {
			throw new Exception(__('LaTeX server location is empty.'));
		}
		elseif (strpos($latex_server,'%s') === false) {
			throw new Exception(__('Field "%s" is required.'));
		}
	}
	
	if (isset($_POST['act_config'])) {
		$core->blog->settings->setNamespace('latex');
		$core->blog->settings->put('latex_server',$latex_server);
		$messages[] = __('Settings have been successfully updated.');
	}
	elseif (isset($_POST['act_test'])) {
		$test = '\!\!\!\oint_{\Sigma}\vec{B}\cdot{\rm d}\vec{S} \ = \ 0';
		$test = remoteLatex::test($test,$latex_server);
	}
	elseif (isset($_POST['act_erase'])) {
		if (!files::deltree($root_path)) {
			throw new Exception(__('Unable to delete cached images.'));
		}
		$messages[] = __('All cached images have been successfully deleted.');
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
<title>Remote LaTeX</title>'.
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

if (isset($test)) {
	echo '<h3>'.__('Test results:').'</h3>'.$test;
}

echo '<div class="multi-part" id="config" title="'.__('Configuration').'">'.
	$forms['admin_cfg'].
	"</div>";
?>
</body></html>
