<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of contextualMenu, a plugin for Dotclear.
# 
# Copyright (c) 2008 Frédéric Leroy
# bestofrisk@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets',array('contextualMenuWidgets','initWidgets'));

class contextualMenuWidgets
{
	public static function initWidgets(&$w)
	{
		$w->create('contextualMenu',__('Contextual Menu'),array('tplcontextualMenu','contextualMenu'));
		$w->contextualMenu->setting('title',__('Title:'),__('Contextual Menu'));
	}
}
?>