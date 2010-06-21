<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Private mode, a plugin for Dotclear 2.
# 
# Copyright (c) 2008-2010 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets',array('privateWidgets','initWidgets'));

class privateWidgets 
{
	public static function initWidgets($w)
	{
		$w->create('privateblog',__('Blog logout'),array('widgetsPrivage','widgetLogout'));
		$w->privateblog->setting('title',__('Title:'),__('Blog logout'));
		$w->privateblog->setting('text',__('Text:'),'','textarea');
		$w->privateblog->setting('label',__('Button:'),__('Disconnect'));
		$w->privateblog->setting('homeonly',__('Home page only'),0,'check');
	}
}
?>