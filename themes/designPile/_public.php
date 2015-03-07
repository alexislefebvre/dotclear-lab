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

l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/public');

# appel css couleur
$core->addBehavior('publicHeadContent','designPileColor_publicHeadContent');

function designPileColor_publicHeadContent($core)
{
	$style = $core->blog->settings->themes->designPileColor;
	if (!preg_match('/^blue|green|pink$/',$style)) {
		$style = 'pink';
	}

	$url = $core->blog->settings->system->themes_url.'/'.$core->blog->settings->system->theme;
	echo '<link rel="stylesheet" type="text/css" media="screen" href="'.$url."/css/".$style.".css\" />\n";
}

# balise d'affichage des liens sociaux
$core->addBehavior('publicTopAfterContent',array('publicDesignPile','publicTopAfterContent'));

class publicDesignPile
{
	public static function publicTopAfterContent($core)
	{
		$separator = ';';
		$social_links;
		$res = '';

		if ($core->blog->settings->themes->designPileSocialLinks) {

			$string = @unserialize($core->blog->settings->themes->designPileSocialLinks);
			$social_links = explode($separator, $string);

			$url = $core->blog->settings->system->themes_url."/".$core->blog->settings->system->theme."/img/social/";

			if($social_links[0] != '') {
				$res .= '<li><a href="'.$social_links[0].'"><img src="'.$url.'ico_twitter.png" alt="Twitter" /></a></li>';
			}
			if($social_links[1] != '') {
				$res .= '<li><a href="'.$social_links[1].'"><img src="'.$url.'ico_facebook.png" alt="Facebook" /></a></li>';
			}
			if($social_links[2] != '') {
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