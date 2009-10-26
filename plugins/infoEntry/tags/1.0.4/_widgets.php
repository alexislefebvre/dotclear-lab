<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of infoEntry, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets',array('infoEntryWidgets','initWidgets'));

class infoEntryWidgets
{
	/**
	 * This function creates the infoEntry's widget object
	 *
	 * @param	w	Widget object
	 */
	public static function initWidgets($w)
	{
		$w->create('infoEntry',__('Information about entry'),array('infoEntryPublic','widget'));
		$w->infoEntry->setting('title',__('Title:'),__('Information about entry'),'text');
		$w->infoEntry->setting('displayauthor',__('Display author'),true,'check');
		$w->infoEntry->setting('displaydate',__('Display date'),true,'check');
		$w->infoEntry->setting('dateformat',__('Date format (leave empty to use default format):'),'','text');
		$w->infoEntry->setting('displaycategory',__('Display category'),true,'check');
		$w->infoEntry->setting('displayfeed',__('Display feed'),true,'check');
		$w->infoEntry->setting('feedformat',__('Feed format:'),'atom','combo',
			array(__('RSS') => 'rss', __('Atom') => 'atom')
		);
		$w->infoEntry->setting('displaytags',__('Display tags'),true,'check');
		$w->infoEntry->setting('sortby',__('Order by:'),'meta_id_lower','combo',
			array(__('Tag name') => 'meta_id_lower', __('Entries count') => 'count')
		);
		$w->infoEntry->setting('orderby',__('Sort:'),'asc','combo',
			array(__('Ascending') => 'asc', __('Descending') => 'desc')
		);
		$w->infoEntry->setting('displaynextentry',__('Display next entry'),true,'check');
		$w->infoEntry->setting('displaypreventry',__('Display previous entry'),true,'check');
		$w->infoEntry->setting('displaynextentrycat',__("Display next same category's entry"),true,'check');
		$w->infoEntry->setting('displaypreventrycat',__("Display previous same category's entry"),true,'check');
	}
}

?>