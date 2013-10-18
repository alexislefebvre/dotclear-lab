<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of categoriesMode, a plugin for Dotclear 2.
#
# Copyright (c) 2007-2011 Adjaya and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets',array('widgetsCategoriesMode','initWidgets'));
 
class widgetsCategoriesMode
{
	# Widget function
	public static function categoriesPageWidgets($w)
	{
		global $core;

		if (!$core->blog->settings->categoriesmode->categoriesmode_active) return;

		if (($w->homeonly == 1 && $core->url->type != 'default') ||
			($w->homeonly == 2 && $core->url->type == 'default')) {
			return;
		}

		$res = ($w->content_only ? '' : '<div class="categories'.($w->class ? ' '.html::escapeHTML($w->class) : '').'">').
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>';

		$res .=
		'<li><strong><a href="'.$core->blog->url.$core->url->getBase("categories").'">'.
		__('All categories').'</a></strong></li>';

		$res .= '</ul>'.
		($w->content_only ? '' : '</div>');
		
		return $res;
	}

	public static function initWidgets($w)
	{
		$w->create('CategoriesPage',__('Categories page'),array('widgetsCategoriesMode','categoriesPageWidgets'));

		$w->CategoriesPage->setting('title',__('Title:'),__('Categories page'),'text');
		$w->CategoriesPage->setting('homeonly',__('Display on:'),0,'combo',
			array(
				__('All pages') => 0,
				__('Home page only') => 1,
				__('Except on home page') => 2
				)
		);
    $w->CategoriesPage->setting('content_only',__('Content only'),0,'check');
    $w->CategoriesPage->setting('class',__('CSS class:'),'');
	}
}
?>
