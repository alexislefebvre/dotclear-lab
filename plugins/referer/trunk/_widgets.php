<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of referer, a plugin for Dotclear.
# 
# Copyright (c) 2008-2010 Tomtom
# http://blog.zenstyle.fr/
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
	public static function initWidgets($w)
	{
		$w->create('top_referer',__('Referer: top referers'),array('refererPublic','top'));
		$w->top_referer->setting('title',__('Title:'),__('Top referers'),'text');
		$w->top_referer->setting('numbertodisplay',__('Number to display:'),'5','combo',
			array('5' => '5', '10' => '10', '15' => '15', '20' => '20')
		);
		$w->top_referer->setting('homeonly',__('Display on:'),0,'combo',
			array(
				__('All pages') => 0,
				__('Home page only') => 1,
				__('Except on home page') => 2
				)
		);
		$w->top_referer->setting('content_only',__('Content only'),0,'check');
		$w->top_referer->setting('class',__('CSS class:'),'');
		$w->top_referer->setting('offline',__('Offline'),0,'check');
		
		$w->create('last_referer',__('Referer: last referers'),array('refererPublic','last'));
		$w->last_referer->setting('title',__('Title:'),__('Last referers'),'text');
		$w->last_referer->setting('numbertodisplay',__('Number to display:'),'5','combo',
			array('5' => '5', '10' => '10', '15' => '15', '20' => '20')
		);
		$w->last_referer->setting('homeonly',__('Display on:'),0,'combo',
			array(
				__('All pages') => 0,
				__('Home page only') => 1,
				__('Except on home page') => 2
				)
		);
		$w->last_referer->setting('content_only',__('Content only'),0,'check');
		$w->last_referer->setting('class',__('CSS class:'),'');
		$w->last_referer->setting('offline',__('Offline'),0,'check');
	}
}