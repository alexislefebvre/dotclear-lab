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

$core->addBehavior('adminDCUpdateException',array('superUpdate','adminDCUpdateException'));

class superUpdate
{
	public static function adminDCUpdateException($e)
	{
		global $core;
		
		if ($e->getCode() != dcUpdate::ERR_FILES_CHANGED) {
			return;
		}
		
		$msg =
		__("Nonetheless, if you are absolutely sure that you never altered those ".
		"files, or don't care about losing your modification, you can ignore ".
		"this warning <strong>at your own risks</strong>. If you are unsure ".
		"about what to do, please just cancel this upgrade.").
		'<p><a href="update.php?step=download">'.__('update anyway').'</a>'.
		' - <a href="update.php?hide_msg=1">'.__('cancel this update').'</a>'.
		'</p>';
		
		$core->error->add($msg);
	}
}
?>
