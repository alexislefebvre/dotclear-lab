<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2.
#
# Copyright (c) 2010 Gaetan Guillard and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets',array('dcSiteMapWidgets','initWidgets'));

class dcSiteMapWidgets
{
	public static function initWidgets($w)
	{
		$w->create('dcSiteMap',__('Site map'),array('tplSiteMap','dcSiteMapWidget'));
		$w->dcSiteMap->setting('title',__('Title:'),__('Site map'));
		$w->dcSiteMap->setting('homeonly',__('Home page only'),0,'check');
	}
}
?>