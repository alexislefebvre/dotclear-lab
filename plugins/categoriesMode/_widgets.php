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

		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}

		$res =
		'<div class="categories">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>';

		$res .=
		'<li><strong><a href="'.$core->blog->url.$core->url->getBase("categories").'">'.
		__('All categories').'</a></strong></li>';

		$res .= '</ul>'.
		'</div>';
		
		return $res;
	}

	public static function initWidgets($w)
	{
		$w->create('CategoriesPage',__('Categories page'),array('widgetsCategoriesMode','categoriesPageWidgets'));

		$w->CategoriesPage->setting('title',__('Title:'),__('Categories page'),'text');
		$w->CategoriesPage->setting('homeonly',__('Home page only'),0,'check');
	}
}
?>
