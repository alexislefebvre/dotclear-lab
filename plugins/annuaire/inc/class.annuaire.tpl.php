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

class dcAnnuaireTPL
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
	* Bloc des elements categories
	*/
	public static function categories($attr, $content)
	{
		if (count(dcAnnuaire::getList()) == 0) {
			return __('No categories in directory');
		}
		
		$res = "";
		
		$res .= '<?php $_ctx->categories = dcAnnuaire::getList();'."?>\n";
		$res .= '<?php while ($_ctx->categories->fetch()) : ?>'.
				$content.
				'<?php endwhile; '.'$_ctx->categories = null; ?>';
		
		return $res;
	}
	
	
	
	/**
	* Titre de la catégorie
	*/
	public static function catTitle($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->categories->title').'; ?>';
	}
	
	/**
	* URL vers la catégorie
	*/
	public static function catURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return self::url().'<?php echo '.sprintf($f,'$_ctx->categories->url').'; ?>';
	}
	
	/**
	* Bloc des blogs
	*/
	public static function sites($attr, $content)
	{
		
		return $content;
		
	}
	
	/**
	* Bloc des éléments blogs
	*/
	public static function sitesEntries($attr, $content)
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
	
		$res .= '<?php if(count($_ctx->sites) > 0) : ?>';
	
		$res .=
		'<?php while ($_ctx->sites->fetch()) : ?>'.$content.'<?php endwhile; '.
		'$_ctx->sites = null; ?>';
		
		$res .= '<?php else : ?>';
		$res .= '<?php echo __( \'Empty category\'); endif; ?>';
		
		return $res;
	}
	
	/**
	* Bloc d'un blog
	*/
	public static function sitesEntry($attr, $content)
	{
		return $content;
	}
	
	/**
	* Titre du blog
	*/
	public static function siteTitle($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'ucfirst($_ctx->sites->blog_name)').'; ?>';
	}
	
	/**
	* URL du blog
	*/
	public static function siteURL($attr)
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
	public static function siteAuthor($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		$res = '<?php $blog = new dcBlog($core, $_ctx->sites->blog_id); ?>';
		$res .= '<?php echo '.sprintf($f,'$blog->settings->setNamespace(\'system\')').'; ?>';
		$res .= '<?php echo '.sprintf($f,'$blog->settings->editor').'; ?>';
		return $res;
		
	}
	
	/**
	* Description du blog
	*/
	public static function siteDesc($attr)
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