<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of plugin multiToc for Dotclear 2.
# Copyright (c) 2008 Thomas Bouron and contributors.
#
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets',array('multiTocWidgets','initWidgets'));

/**
 * Class infoBlogWidgets
 */
class multiTocWidgets
{
	/**
	 * This function creates the MultiToc's widget object
	 *
	 * @param	w	Widget object
	 */
	public static function initWidgets(&$w)
	{
		$w->create('multiToc',__('Table of content'),array('multiTocPublic','widget'));
		$w->multiToc->setting('title',__('Title:'),__('Table of content'));
		$w->multiToc->setting('homeonly',__('Home page only'),true,'check');
	}
}

?>
