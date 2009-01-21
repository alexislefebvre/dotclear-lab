<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of referer, a plugin for Dotclear.
# 
# Copyright (c) 2008 Tomtom
# http://blog.zesntyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets',array('refererWidgets','initWidgets'));

class refererWidgets
{
	/**
	 * This function creates the referer's widgets object
	 *
	 * @param	w	Widget object
	 */
	public static function initWidgets(&$w)
	{
		$w->create('top_referer',__('Top referers'),array('refererPublic','top'));
		$w->top_referer->setting('title',__('Title:'),__('Top referers'),'text');
		$w->top_referer->setting('numbertodisplay',__('Number to display:'),'5','combo',
			array('5' => '5', '10' => '10', '15' => '15', '20' => '20')
		);
		$w->top_referer->setting('homeonly',__('Home page only'),true,'check');
		
		$w->create('last_referer',__('Last referers'),array('refererPublic','last'));
		$w->last_referer->setting('title',__('Title:'),__('Last referers'),'text');
		$w->last_referer->setting('numbertodisplay',__('Number to display:'),'5','combo',
			array('5' => '5', '10' => '10', '15' => '15', '20' => '20')
		);
		$w->last_referer->setting('homeonly',__('Home page only'),true,'check');
	}
}

?>
