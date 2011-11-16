<?php
# -- BEGIN LICENSE BLOCK ---------------------------------------
#
# This file is part of Dotclear 2.
#
# Copyright (c) 2003-2011 Olivier Meunier & Association Dotclear
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK -----------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

if (basename($_SERVER['SCRIPT_NAME']) != 'update.php') {
	return;
}

if (empty($_GET['step']) || $_GET['step'] != 'check') {
	return;
}

$core->addBehavior('adminPageHTMLHead',array('superUpdate','adminPageHTMLHead'));

class superUpdate
{
	public static function adminPageHTMLHead()
	{
		$forced_update_warning =
		__("Nonetheless, if you are absolutely sure that you never altered those ".
		"files, or don't care about losing your modification, you can ignore ".
		"this warning <strong>at your own risks</strong>. If you are unsure ".
		"about what to do, please just cancel this upgrade.");
		
		echo
		'<script type="text/javascript" src="index.php?pf=superUpdate/update.js"></script>'."\n".
		'<script type="text/javascript">'."\n".
		"//<![CDATA[\n".
		"dotclear.msg.forced_update_warning = '",addcslashes($forced_update_warning,"'")."';\n".
		dcPage::jsVar('dotclear.msg.update_anyway',__('update anyway'))."\n".
		dcPage::jsVar('dotclear.msg.cancel_update',__('cancel this update'))."\n".
		"\n//]]>\n".
		"</script>\n";
	}
}
?>
