<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of multiToc, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom and contributors
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets',array('multiTocWidgets','initWidgets'));

class multiTocWidgets
{
	/**
	 * This function creates the MultiToc's widget object
	 *
	 * @param	w	Widget object
	 */
	public static function initWidgets($w)
	{
		$w->create('multiToc',__('Table of content'),array('multiTocPublic','widget'));
		$w->multiToc->setting('title',__('Title:'),__('Table of content'));
		$w->multiToc->setting('homeonly',__('Home page only'),true,'check');
	}
}

?>
