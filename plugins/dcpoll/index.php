<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'DC Poll', a plugin for Dotclear 2                 *
 *                                                             *
 *  Copyright (c) 2007                                         *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'DC Poll' (see COPYING.txt);            *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/
if (!defined('DC_CONTEXT_ADMIN')) { return; }

try
{
	$messages = array();
	$default_tab = empty($_GET['tab']) ? false : html::escapeHTML($_GET['tab']);
}

catch (Exception $e)
{
	$core->error->add($e->getMessage());
}

/* DISPLAY
--------------------------------------------------- */

// Notifications
if (!empty($_GET['up']))
{
	switch ($_GET['up']):
	case 'settings':
		$messages[] = __('Configuration updated.');
		break;
	case 'newad':
		$messages[] = __('New ad added.');
		$default_tab = 'nea_list';
		break;
	default:
		$messages[] = __('Something was maybe updated. But what ?');
	endswitch;
}

// Headers
echo '
<html><head>
<title>'.__('Polls').'</title>

'.dcPage::jsToolMan().
($default_tab ? dcPage::jsPageTabs($default_tab) : '').'

</head><body>
<h2>'.__('Polls').'</h2>'."\n\n";

if (!empty($messages))
{
	foreach ($messages as $message)
	{
		echo '	<p class="message">'.$message."</p>\n";
	}
}

// Content
include dirname(__FILE__).'/forms.php';

if (!empty($_REQUEST['edit'])) {
	echo
		'<p><a href="'.$p_url.'&amp;tab=poll_list" title="'.__('Return to the main configuration page').'">'.
		__('Go back').'</a></p>'.
		$poll_forms['edit'];
}
else {
	echo
		'<div class="multi-part" id="poll_config" title="'.__('Polls configuration').'">'.
		$poll_forms['config'].
		"</div>\n\n".
		'<div class="multi-part" id="poll_list" title="'.__('Polls list').'">'.
		$poll_forms['list'].
		"</div>\n\n".
		'<div class="multi-part" id="poll_edit" title="'.__('New poll').'">'.
		$poll_forms['edit'].
		"</div>\n\n".
		'<div class="multi-part" id="poll_help" title="'.__('Help').'">'.
		$poll_forms['help'].
		"</div>\n\n";
}
echo '</body></html>';
?>