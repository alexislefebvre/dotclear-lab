<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of community, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets',array('communityWidgets','initWidgets'));

class communityWidgets
{
	/**
	 * This function creates the signUp's widget object
	 *
	 * @param	w	Widget object
	 */
	public static function initWidgets(&$w)
	{
		$w->create('community',__('Community'),array('communityPublic','widget'));
		$w->community->setting('title',__('Title:'),__('Community'),'text');
		$w->community->setting('homeonly',__('Home page only'),0,'check');
	}
}

?>