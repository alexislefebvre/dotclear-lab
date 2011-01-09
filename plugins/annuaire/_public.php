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

require_once dirname(__FILE__).'/class.annuaire.widgets.php';

//url
$core->url->register('annuaire','annuaire','^annuaire(/(.*))$',array('publicAnnuaire','load'));

//templates
$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/templates');

$core->tpl->addValue('AnnuaireURL',array('tplAnnuaire','url'));

$core->tpl->addBlock('Annuaire', array('tplAnnuaire', 'annuaire'));

$core->tpl->addBlock('AnnuaireCategories', array('tplAnnuaire', 'annuaireCategories'));
$core->tpl->addBlock('AnnuaireCategoriesEntries', array('tplAnnuaire', 'annuaireCategoriesEntries'));
$core->tpl->addBlock('AnnuaireCategoriesEntry', array('tplAnnuaire', 'annuaireCategoriesEntry'));

$core->tpl->addValue('AnnuaireCatTitle', array('tplAnnuaire', 'annuaireCatTitle'));
$core->tpl->addValue('AnnuaireCatURL', array('tplAnnuaire', 'annuaireCatURL'));

$core->tpl->addBlock('AnnuaireSites', array('tplAnnuaire', 'annuaireSites'));
$core->tpl->addBlock('AnnuaireSitesEntries', array('tplAnnuaire', 'annuaireSitesEntries'));
$core->tpl->addBlock('AnnuaireSitesEntry', array('tplAnnuaire', 'annuaireSitesEntry'));

$core->tpl->addValue('AnnuaireSiteTitle', array('tplAnnuaire', 'annuaireSiteTitle'));
$core->tpl->addValue('AnnuaireSiteURL', array('tplAnnuaire', 'annuaireSiteURL'));
$core->tpl->addValue('AnnuaireSiteAuthor', array('tplAnnuaire', 'annuaireSiteAuthor'));
$core->tpl->addValue('AnnuaireSiteDesc', array('tplAnnuaire', 'annuaireSiteDesc'));


class publicAnnuaire extends dcUrlHandlers
{
	public static function load($args) {
		$_ctx =& $GLOBALS['_ctx'];
		$_ctx->annuaire = basename($args);
		
		$catUrl = $_ctx->annuaire;
		if(empty($catUrl)) {
			self::serveDocument('annuaire_list.html');
		} else {
			self::serveDocument('annuaire_categorie.html');
		}
		exit;
	}
}


class tplAnnuaire
{
	/**
	* URL de l'annnuaire
	*/
	public static function url() {
		global $core;
		return $core->blog->url."annuaire/";
	}
	
	/**
	*Bloc d'affichage de l'annuaire
	*/
	public static function annuaire($attr, $content)
	{
		$res = "";
		
		$res .= '<?php  if($_ctx->annuaire) {'.
				'$_ctx->categories = dcAnnuaire::getFromURL($_ctx->annuaire);'.
				'$_ctx->categories->fetch(); } ?>';	
		$res .= $content;
		
		return $res;
	}
	
	/**
	* Bloc des categories
	*/
	public static function annuaireCategories($attr, $content)
	{
		$res="";
		
		if (count(dcAnnuaire::getList()) == 0) {
			$res .= __('No categories in directory');
		} else {
			$res .= $content;
		}
		
		return $res;
		
	}
	
	/**
	* Bloc des elements categories
	*/
	public static function annuaireCategoriesEntries($attr, $content)
	{
		$res = "";
		
		$res .= '<?php $_ctx->categories = dcAnnuaire::getList();'."?>\n";
		$res .= '<?php while ($_ctx->categories->fetch()) : ?>'.
				$content.
				'<?php endwhile; '.'$_ctx->categories = null; ?>';
		
		return $res;
	}
	
	/**
	* Bloc  d'une catégorie
	*/
	public static function annuaireCategoriesEntry($attr, $content)
	{
		return $content;
	}
	
	/**
	* Titre de la catégorie
	*/
	public static function annuaireCatTitle($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->categories->title').'; ?>';
	}
	
	/**
	* URL vers la catégorie
	*/
	public static function annuaireCatURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return self::url().'<?php echo '.sprintf($f,'$_ctx->categories->url').'; ?>';
	}
	
	/**
	* Bloc des blogs
	*/
	public static function annuaireSites($attr, $content)
	{
		return $content;
	}
	
	/**
	* Bloc des éléments blogs
	*/
	public static function annuaireSitesEntries($attr, $content)
	{
		$res = "";
		
		$lastn = 0;
		if (isset($attr['lastn'])) {
			$lastn = abs((integer) $attr['lastn'])+0;
		}
		
		$res .= "<?php\n";
		$res.= '$_ctx->sites = dcAnnuaire::getBlogs($_ctx->categories->category_id';
		if($lastn != 0) {
			$res .= ', '.$lastn;
		}
		$res .= ');'."?>\n";
	
		$res .=
		'<?php while ($_ctx->sites->fetch()) : ?>'.$content.'<?php endwhile; '.
		'$_ctx->sites = null; ?>';
		
		return $res;
	}
	
	/**
	* Bloc d'un blog
	*/
	public static function annuaireSitesEntry($attr, $content)
	{
		return $content;
	}
	
	/**
	* Titre du blog
	*/
	public static function annuaireSiteTitle($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'ucfirst($_ctx->sites->blog_name)').'; ?>';
	}
	
	/**
	* URL du blog
	*/
	public static function annuaireSiteURL($attr)
	{
		$res= "";
		$_ctx =& $GLOBALS['_ctx'];
		$f = $GLOBALS['core']->tpl->getFilters($attr);
				
		if(isset($attr['short'])) {
			$res .= '<?php echo '.sprintf($f,'dcAnnuaire::shortURL($_ctx->sites->blog_url, 7)').'; ?>';
		} else {
			$res .= '<?php echo '.sprintf($f,'$_ctx->sites->blog_url').'; ?>';
		}
		
		return $res;
	}
	
	/**
	* Auteur du blog
	*/	
	public static function annuaireSiteAuthor($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		$res = '<?php $blog = new dcBlog($core, $_ctx->sites->blog_id); ?>';
		$res .= '<?php echo '.sprintf($f,'$blog->settings->get(\'editor\')').'; ?>';
		return $res;
		
	}
	
	/**
	* Description du blog
	*/
	public static function annuaireSiteDesc($attr)
	{
		$res = "";
		$f = $GLOBALS['core']->tpl->getFilters($attr);
				
		$res .= '<?php if($_ctx->sites->blog_desc) { '.
				'echo '.sprintf($f,'text::cutString($_ctx->sites->blog_desc, 150)').';'.
				' echo("...");'.
				'} else { echo("NC"); }'.
				'?>';
		
		return $res;
	}
	
}

?>