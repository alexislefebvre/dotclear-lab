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

require dirname(__FILE__).'/_widget.php';

class publicAdvancedTagList
{
	public static function advancedTagList(&$w)
	{
		global $core;
		
		$limit = abs((integer) $w->limit);
		
		$objMeta = new dcMeta($core);
		$rs = $objMeta->getMeta('tag',$limit);
		
		if ($rs->isEmpty()) {
			return;
		}
		
		if ($w->homeonly && $core->url->type != 'default') {
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
		
		$res =
		'<div class="tags">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>';

		while ($rs->fetch()) {
			$k = $rs->meta_id;
			if ($w->$k) {
				$res .=
				'<li><a href="'.$core->blog->url.$core->url->getBase('tag').'/'.rawurlencode($rs->meta_id).'">'.
				html::escapeHTML($rs->meta_id).'</a>'.
				($w->postcount ? ' ('.$rs->count.')' : '').
				'</li>';
			}
		}
		$res .= '</ul>';
		$res .= '</div>';
		
		return $res;
	}
}
?>