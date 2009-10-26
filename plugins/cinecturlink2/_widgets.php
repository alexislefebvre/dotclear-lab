<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of cinecturlink2, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

$core->addBehavior('initWidgets',array('cinecturlink2AdminWidget','links'));

class cinecturlink2AdminWidget
{
	public static function links($w)
	{
		global $core;

		$C2 = new cinecturlink2($core);

		$categories_combo = array('' => '', __('Uncategorized') => 'null');
		$categories = $C2->getCategories();
		while($categories->fetch())
		{
			$cat_title = html::escapeHTML($categories->cat_title);
			$categories_combo[$cat_title] = $categories->cat_id;
		}

		$sortby_combo = array(
			__('Update date') => 'link_upddt',
			__('My rating') => 'link_note',
			__('Title') => 'link_title',
			__('Random') => 'RANDOM'
		);
		$order_combo = array(
			__('Ascending') => 'asc',
			__('Descending') => 'desc'
		);

		$w->create('cinecturlink2links',
			__('My cinecturlink'),array('cinecturlink2PublicWidget','links')
		);
		$w->cinecturlink2links->setting('title',
			__('Title:'),__('My cinecturlink'),'text'
		);
		$w->cinecturlink2links->setting('category',
			__('Category:'),'','combo',$categories_combo
		);
		$w->cinecturlink2links->setting('sortby',
			__('Order by:'),'link_upddt','combo',$sortby_combo
		);
		$w->cinecturlink2links->setting('sort',
			__('Sort:'),'desc','combo',$order_combo
		);
		$w->cinecturlink2links->setting('limit',
			__('Limit:'),10,'text'
		);
		$w->cinecturlink2links->setting('withlink',
			__('Enable link'),1,'check'
		);
		$w->cinecturlink2links->setting('shownote',
			__('Show my rating'),0,'check'
		);
		$w->cinecturlink2links->setting('showdesc',
			__('Show description'),0,'check'
		);
		$w->cinecturlink2links->setting('homeonly',
			__('Home page only'),1,'check'
		);
	}
}
?>