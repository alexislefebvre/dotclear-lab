<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2.
#
# Copyright (c) 2003-2009 Olivier Meunier and contributors
# Modifications by Jean-Christophe Dubacq
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

class translatedWidgets
{
	public static function tagsWidget(&$w)
	{
		global $core;
		
		$limit = abs((integer) $w->limit);
		
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
		
		$res =
            '<div class="tags">'.
            ($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
            '<ul>';
		
		while ($rs->fetch())
            {
                $res .=
                    '<li><a href="'.$core->blog->url.$core->url->getBase('tag').'/'.rawurlencode($rs->meta_id).'" '.
                    'class="tag'.$rs->roundpercent.'">'.
                    __($rs->meta_id).'</a> </li>';
            }
		
		$res .= '</ul>';
		
        if ($core->url->getBase('tags'))
            {
                $res .=
                    '<p><strong><a href="'.$core->blog->url.$core->url->getBase("tags").'">'.
                    __('All tags').'</a></strong></p>';
            }
		
		$res .= '</div>';
		
		return $res;
	}
}
?>