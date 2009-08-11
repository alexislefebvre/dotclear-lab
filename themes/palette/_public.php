<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Palette, a theme for Dotclear.
#
# Copyright (c) 2009 annso
# contact@as-i-am.fr
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }


$core->addBehavior('publicHeadContent',array('publicPalette','publicHeadContent'));

$core->tpl->addValue('paletteTabs',array('tplPalette','paletteTabs'));

class publicPalette
{
	public static function publicHeadContent(&$core)
	{
		$default_colors = array('#C5D984', '#AFCF48', '#E69B32', '#F75823', '#FF0335');
		$colors[]= array();
		$separator = ',';

		$palette_colors;
		if ($core->blog->settings->palette_colors) {
			$palette_colors = @unserialize($core->blog->settings->palette_colors);
			$colors = explode($separator, $palette_colors);
		} else {
			$palette_colors = implode($separator, $default_colors);
			$core->blog->settings->setNameSpace('palette');
			$core->blog->settings->put('palette_colors',serialize($palette_colors),'string');
			$core->blog->triggerBlog();
			$colors = $default_colors;
		}
		
		$css= '';
		if(file_exists(dirname(__FILE__).'/palette.css')) {
			$css = file_get_contents(dirname(__FILE__).'/palette.css');
			for($i=0; $i<5; $i++) {
				$css = ereg_replace('%color'.$i.'%', $colors[$i], $css);
			}
			echo '<style type="text/css" media="screen">'.$css.'</style>';
		}
	}

}

class tplPalette
{
	public static function paletteTabs($attr)
	{
		$separator = ','; $separator_links = ' ';
		$res = '';
		$tab_classes = array('one', 'two', 'three', 'four', 'five');
		
		if ($GLOBALS['core']->blog->settings->palette_names) {
			
			$palette_names = @unserialize($GLOBALS['core']->blog->settings->palette_names);
			$tab_names = explode($separator, $palette_names);
			
			if ($GLOBALS['core']->blog->settings->palette_links) {
				
				$palette_links = @unserialize($GLOBALS['core']->blog->settings->palette_links);
				$tab_links = explode($separator_links, $palette_links);
			
				for($i=0; $i<5; $i++) {
					if($tab_names[$i]) {
						$res .= '<li class=\"'.$tab_classes[$i].'\">';
						if($tab_links[$i]) $res .= '<a href=\"'.$tab_links[$i].'\">'.$tab_names[$i].'</a>';
						else $res .= $tab_names[$i];
						$res .= '</li>';
					}
				}
				
				$res = sprintf('<ul class=\"menu\">%s</ul>', $res);
				$res = sprintf('<div id=\"navigation\">%s</div>', $res);
			
			}
		}

		return '<?php echo "'.$res.'"; ?>';
	}
}
?>
