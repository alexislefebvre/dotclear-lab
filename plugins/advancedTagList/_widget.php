<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of AdvancedTagList, a plugin for Dotclear 2.
#
# Copyright (c) 2009 Philippe Amalgame and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

$core->addBehavior('initWidgets',array('avancedTagListBehaviors','initWidgets'));

class avancedTagListBehaviors
{
	
	public static function initWidgets(&$w)
	{
		global $core;
		$w->create('AdvancedTagList',__('Advanced tags list'),array('publicAdvancedTagList','advancedTagList'));
		$w->AdvancedTagList->setting('homeonly',__('Home page only'),1,'check');
		$w->AdvancedTagList->setting('postcount',__('With entries counts'),0,'check');
		$w->AdvancedTagList->setting('title',__('Title:'),__('Tags'));
		$w->AdvancedTagList->setting('limit',__('Limit (empty means no limit):'),'20');
		$w->AdvancedTagList->setting('sortby',__('Order by:'),'meta_id_lower','combo',
			array(__('Tag name') => 'meta_id_lower', __('Entries count') => 'count')
		);
		$w->AdvancedTagList->setting('orderby',__('Sort:'),'asc','combo',
			array(__('Ascending') => 'asc', __('Descending') => 'desc')
		);
		
		$limit = '';
		
		$objMeta = new dcMeta($core);
		$rs = $objMeta->getMeta('tag',$limit);
		
		if ($rs->isEmpty()) {
			return;
		}
		
		$sort = $w->sortby;
		if (!in_array($sort,array('meta_id_lower','count'))) {
			$sort = 'meta_id_lower';
		}
		
		$order = $w->orderby;
		if ($order != 'asc') {
			$order = 'desc';
		}
		
		$rs->sort($sort,$order);
		
		#$rs = $core->blog->getCategories($params);
		if (!$rs->isEmpty()) {
	        while ($rs->fetch()) {
				$w->AdvancedTagList->setting($rs->meta_id,html::escapeHTML($rs->meta_id),true,'check');			
			}
		}
	}
}
?>