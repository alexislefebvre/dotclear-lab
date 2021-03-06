<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of multiBlogSearch, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets',array('multiBlogSearchWidgets','initWidgets'));

class multiBlogSearchWidgets
{
	public static function initWidgets($w)
	{
		$w->create('multiBlogSearch',__('Multi blog search'),array('multiBlogSearchPublic','widget'));
		$w->multiBlogSearch->setting('title',__('Title:'),__('Multi blog search'));
		$w->multiBlogSearch->setting('homeonly',__('Home page only'),true,'check');
	}
}

?>