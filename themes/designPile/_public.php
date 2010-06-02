<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of designPile, a theme for Dotclear.
#
# Original Wordpress Theme from Site5
# http://www.site5.com/wordpress-themes/
#
# Copyright (c) 2010
# annso contact@as-i-am.fr
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

# appel du bon fichier css dans le head
$core->addBehavior('publicHeadContent',array('publicDesignPile','publicHeadContent'));
# balise d'affichage des liens sociaux
$core->addBehavior('publicTopAfterContent',array('publicDesignPile','publicTopAfterContent'));

# première page personnalisée
$core->url->registerDefault(array('urlHandlersDesignPile','home'));


class publicDesignPile
{
	public static function publicHeadContent(&$core)
	{
	
		$color;
		if ($core->blog->settings->designPileColor) {
			$color = @unserialize($core->blog->settings->designPileColor);
		} else {
			$color = "pink";
			$core->blog->settings->setNameSpace('designPile');
			$core->blog->settings->put('designPileColor',serialize($color),'string');
			$core->blog->triggerBlog();
		}

		if(file_exists(dirname(__FILE__).'/css/'.$color.'.css')) {
			$css = '/css/'.$color.'.css';
		} else {
			$css = '/css/pink.css';
		}
		$url = $core->blog->settings->themes_url."/".$core->blog->settings->theme."/";
		echo '<style type="text/css">';
		echo '@import url('.$url.$css.');';
		echo '</style>';
		
	}

	public static function publicTopAfterContent(&$core)
	{
		$separator = ' ';
		$social_links;
		$res = '';

		if ($core->blog->settings->designPileSocialLinks) {
			
			$social_links = unserialize($core->blog->settings->designPileSocialLinks);

			$url = $core->blog->settings->themes_url."/".$core->blog->settings->theme."/img/social/";
			
			if($social_links[0] != "") {
				$res .= '<li><a href="'.$social_links[0].'"><img src="'.$url.'ico_twitter.png" alt="Twitter" /></a></li>';
			}
			if($social_links[1] != "") {
				$res .= '<li><a href="'.$social_links[1].'"><img src="'.$url.'ico_facebook.png" alt="Facebook" /></a></li>';
			}
			if($social_links[2] != "") {
				$res .= '<li><a href="'.$social_links[2].'"><img src="'.$url.'ico_rss.png" alt="RSS" /></a></li>';
			}

			if($res != '') {
				$res = sprintf('<ul id="socialLinks">%s</ul>', $res);
				$res = '<div id="getSocial"></div>'.$res;
			}
					
		} 
		
		echo $res;
		
	}
	
}

class urlHandlersDesignPile extends dcUrlHandlers
{
	public static function home($args)
	{
		$core =& $GLOBALS['core'];
		
		$n = self::getPageNumber($args);
		
		if ($args && !$n)
		{
			self::p404();
		}
		if (!$n && empty($_GET['q']))
		{
			# The entry
			$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
			//header('Pragma: no-cache');
			//header('Cache-Control: no-cache');
			self::serveDocument('home-page1.html');
			$core->blog->publishScheduledEntries();
			exit;
		}		
		else
		{
			if ($n) {
				$GLOBALS['_page_number'] = $n;
				$core->url->type = $n > 0 ? 'defaut-page' : 'default';
			}
			
			if (empty($_GET['q'])) {
				self::serveDocument('home.html');
				$core->blog->publishScheduledEntries();
				exit;
			} else {
				self::search();
			}
		}
	}
}

?>
