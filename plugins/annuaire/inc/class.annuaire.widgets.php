<?php
# ***** BEGIN LICENSE BLOCK *****
# This file a plugin of DotClear.
# Copyright (c) Marc Vachette and Aurelien Gerits. All rights
# reserved.
#
#Annuaire is free software; you can redistribute it and/or modify
# it under the terms of the Creative Commons License BY SA
# see the page http://creativecommons.org/licenses/by-sa/3.0/ for more information
# 
# Annuaire is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# Creative Commons License for more details.
#
# ***** END LICENSE BLOCK *****
 
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