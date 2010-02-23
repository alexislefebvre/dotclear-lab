<?php
# vim: set noexpandtab tabstop=5 shiftwidth=5:
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of footnotesToolbar, a plugin for Dotclear.
# 
# Copyright (c) 2009 Aurélien Bompard <aurelien@bompard.org>
# 
# Licensed under the AGPL version 3.0.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/agpl-3.0.html
# -- END LICENSE BLOCK ------------------------------------

$core->addBehavior('adminPostHeaders',array('footnotesToolbarBehaviors','postHeaders'));
$core->addBehavior('adminRelatedHeaders',array('footnotesToolbarBehaviors','postHeaders'));

class footnotesToolbarBehaviors
{
	public static function postHeaders()
	{
		return
		'<script type="text/javascript" src="index.php?pf=footnotesToolbar/footnotesToolbar.js"></script>'.
		'<script type="text/javascript">'."\n".
		"//<![CDATA[\n".
		dcPage::jsVar('jsToolBar.prototype.elements.footnotes.title','Footnotes')."\n".
		dcPage::jsVar('jsToolBar.prototype.elements.footnotes.section_name',__('Notes:'))."\n".
		"//]]>\n".
		"</script>\n";
	}
}
?>
