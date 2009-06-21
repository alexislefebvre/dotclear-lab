<?php
# vim: set noexpandtab tabstop=5 shiftwidth=5:
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of odt, a plugin for Dotclear.
# 
# Copyright (c) 2009 Aurélien Bompard <aurelien@bompard.org>
# 
# Licensed under the AGPL version 3.0.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/agpl-3.0.html
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets',array('odtWidgets','initWidgets'));

class odtWidgets
{
	public static function initWidgets(&$w)
	{
		$w->create('odt',__("Export to ODT"),array('tplOdt','odtWidget'));
		$w->odt->setting('title',__('Title:'),__('Export'));
		$w->odt->setting('link_title',__('Link title:'),__('Export to ODT'));
		$w->odt->setting('onhome',__('On the home page'),0,'check');
	}
}
?>
