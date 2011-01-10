<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Annuaire, a plugin for Dotclear.
# 
# Copyright (c) 2010 Marc Vachette
# marc.vachette@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
 
class annuaireWidgets
{
	//liste des categories
	public static function annuaireCategories(&$w)
	{
		global $core;
		$res="";
		
		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}
		
		// récupçre la liste complète des categories
		$categories = dcAnnuaire::getList();
		if (is_object($categories) === FALSE)
			return __('No categories');
		
				
		$res .= ($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '');
		$res .= '<ul id="blog_categories">';
		
		$categories->moveStart();
		while ($categories->fetch()) {
			$k = (integer) $categories->category_id;
			$res .= '<li><a href="'.$core->blog->url.'annuaire/'.$categories->url.'/">'.$categories->title.'</a></li>';
		}
		
		$res .= '</ul>';
		
		return $res;
	}

}
?>