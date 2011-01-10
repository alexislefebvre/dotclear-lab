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