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

if (!defined('DC_CONTEXT_ADMIN')) exit;

//widgets
$core->addBehavior('initWidgets',array('annuaireBehaviors','initWidgets'));

//behaviors
$core->addBehavior('adminBlogPreferencesForm',array('annuaireBehaviors','adminBlogPreferencesForm'));
$core->addBehavior('adminBeforeBlogUpdate',array('annuaireBehaviors','adminBeforeBlogUpdate'));

//menu admin
$_menu['Plugins']->addItem(__('Directory'),
							'plugin.php?p=annuaire',
							'index.php?pf=annuaire/icon.png',
							preg_match('/plugin.php\?p=annuaire(&.*)?$/',$_SERVER['REQUEST_URI'])
						);
						
						
						
class annuaireBehaviors {

	/**
	* formulaire de choix de la catégorie dans les paramètres du blog
	*/
	public static function adminBlogPreferencesForm($core)
	{
		$categories = dcAnnuaire::getList();
		
		if(0 === $categories) {
			return;
		}
		
		$list[__('none')] = 0;
		$categories->moveStart();
		while ($categories->fetch()) {
			$list[$categories->title] = $categories->category_id;
		}
		
		$core->blog->settings->setNamespace('annuaire');
		$selected = $core->blog->settings->category;
			
		
		echo('<fieldset><legend>'.__('Blog category').'</legend>'.
		'<p><label class="classic">'.
		__('Category of this blog:').' '.
		form::combo('category',$list,$selected).
		
		'</label></p>'.
		'</fieldset>');
	}
	
	/**
	* enregistrement de la catégorie dans les settings du blog
	*/
	public static function adminBeforeBlogUpdate($cur, $blog_id) {
		global $core;

		$core->blog->settings->setNamespace('annuaire');
		$core->blog->settings->put('category', $_POST['category'], 'integer');
		$core->blog->triggerBlog();
	}

	/**
	* initialisation des widgets
	*/
	public static function initWidgets(&$w)
	{
		//liste des categories		
		$w->create('annuaireCategories',__('Directory (categories list)'),array('annuaireWidgets','annuaireCategories'));
		$w->annuaireCategories->setting('homeonly',__('Home page only'),0,'check');			
		$w->annuaireCategories->setting('title',__('Title:'),__('Directory (categories list)'),'text');
	}


}						

?>