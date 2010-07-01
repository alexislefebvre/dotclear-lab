<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of ColorBox, a plugin for Dotclear 2.
#
# Copyright (c) 2009 Philippe Amalgame and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

$core->addBehavior('publicHeadContent',array('colorboxPublic','publicHeadContent'));

class colorboxPublic
{
	public static function publicHeadContent($core)
	{
		# Settings compatibility test
		if (!version_compare(DC_VERSION,'2.1.7','<=')) {
			$s =& $core->blog->settings->colorbox;
		} else {
			$core->blog->settings->setNamespace('colorbox');
			$s =& $core->blog->settings;
		}
		
		if (!$s->colorbox_enabled) {
			return;
		}
		
		$url = $core->blog->getQmarkURL().'pf='.basename(dirname(__FILE__));
		
		$icon_name = 'zoom.png';
		$icon_width = '16';
		$icon_height = '16';
		
		echo
		'<style type="text/css">'."\n".
		'@import url('.$url.'/css/colorbox_common.css);'."\n".
		"</style>\n".
		'<style type="text/css">'."\n".
		'@import url('.$url.'/themes/'.$s->colorbox_theme.'/colorbox_theme.css);'."\n".
		"</style>\n";
		
		if ($s->colorbox_user_files) {
			$public_path = $core->blog->public_path;
			$public_url = $core->blog->settings->system->public_url;		
			$colorbox_user_path = $public_path.'/colorbox/themes/';
			$colorbox_user_url = $public_url.'/colorbox/themes/';
			
			if (file_exists($colorbox_user_path.$s->colorbox_theme.'/colorbox_user.css')){
				echo
				'<style type="text/css">'."\n".
				'@import url('.$colorbox_user_url.$s->colorbox_theme.'/colorbox_user.css);'."\n".
				"</style>\n";
			}
		} else {
			$theme_path = path::fullFromRoot($core->blog->settings->system->themes_path."/".$core->blog->settings->system->theme,DC_ROOT);;
			$theme_url = $core->blog->settings->system->themes_url."/".$core->blog->settings->system->theme;
			$colorbox_user_path = $theme_path.'/colorbox/themes/'.$s->colorbox_theme.'/colorbox_user.css';
			$colorbox_user_url = $theme_url.'/colorbox/themes/'.$s->colorbox_theme.'/colorbox_user.css';
			if (file_exists($colorbox_user_path)){
			echo
				'<style type="text/css">'."\n".
				'@import url('.$colorbox_user_url.');'."\n".
				"</style>\n";
			};

		}
		echo
		'<script type="text/javascript" src="'.$url.'/js/jquery.colorbox-min.js"></script>'."\n".
		'<script type="text/javascript">'."\n".
		"//<![CDATA[\n";
		
		$selectors = 'div.post'.($s->colorbox_selectors !== '' ? ','.$s->colorbox_selectors : '');
		
		echo
		'$(window).load(function(){'.
			'var count = 0; '.
			'$("'.$selectors.'").each(function() {'."\n".
				'count++;'."\n".
				'$(this).find("a[href$=.jpg],a[href$=.jpeg],a[href$=.png],a[href$=.gif],'.
				'a[href$=.JPG],a[href$=.JPEG],a[href$=.PNG],a[href$=.GIF]").addClass("colorbox_zoom");'."\n".
				'$(this).find("a[href$=.jpg],a[href$=.jpeg],a[href$=.png],a[href$=.gif],'.
				'a[href$=.JPG],a[href$=.JPEG],a[href$=.PNG],a[href$=.GIF]").attr("rel", "colorbox-"+count);'."\n";
		
		echo
		'$(this).find("a.colorbox_zoom").each(function(){'."\n".
			'var p = $(this).find("img");'."\n".
			'if (p.attr("title") != 0 && $(this).attr("title") == 0){'."\n".
				'$(this).attr("title", p.attr("title"));'."\n".
			'}'."\n".
		'});'."\n";
				
		if ($s->colorbox_zoom_icon_permanent)
		{
			echo
			'$(this).find("a.colorbox_zoom").each(function(){'."\n".
				'var p = $(this).find("img");'."\n".
				'if (p.length != 0){'."\n".
					'var offset = p.offset();'."\n".
					'var parent = p.offsetParent();'."\n".
					'var offsetparent = parent.offset();'."\n".
					'var parenttop = offsetparent.top;'."\n".
					'var parentleft = offsetparent.left;'."\n".
					'var top = offset.top-parenttop;'."\n";
						
			if ($s->colorbox_position) {
				echo 'var left = offset.left-parentleft;'."\n";
			} else {		
				echo 'var left = offset.left-parentleft+p.outerWidth()-'.$icon_width.';'."\n";
			}
			
			echo '$(this).append("<span style=\"z-index:10;width:'.$icon_width.'px;height:'.$icon_height.'px;top:'.'"+top+"'.'px;left:'.'"+left+"'.'px;background: url('.html::escapeJS($url).'/themes/'.$s->colorbox_theme.'/images/zoom.png) top left no-repeat; position:absolute;\"></span>");'."\n".
				'}'."\n".
			'});'."\n";
		}
		
		if ($s->colorbox_zoom_icon && !$s->colorbox_zoom_icon_permanent)
		{
			echo
			'$(\'body\').prepend(\'<img id="colorbox_magnify" style="display:block;padding:0;margin:0;z-index:10;width:'.$icon_width.'px;height:'.$icon_height.'px;position:absolute;top:0;left:0;display:none;" src="'.html::escapeJS($url).'/themes/'.$s->colorbox_theme.'/images/zoom.png" alt=""  />\');'."\n".
			'$(\'img#colorbox_magnify\').click(function ()'."\n".
				'{ '."\n".
					'$("a.colorbox_zoom img.colorbox_hovered").click(); '."\n".
					'$("a.colorbox_zoom img.colorbox_hovered").removeClass(\'colorbox_hovered\');'."\n".
				'});'."\n".
				'$(\'a.colorbox_zoom img\').click(function ()'."\n". 
				'{ '."\n".
					'$(this).removeClass(\'colorbox_hovered\');'."\n".
				'});'."\n".
				'$("a.colorbox_zoom img").hover(function(){'."\n".
				'var p = $(this);'."\n".
				'p.addClass(\'colorbox_hovered\');'."\n".
				'var offset = p.offset();'."\n";

			if (!$s->colorbox_position) {
				echo '$(\'img#colorbox_magnify\').css({\'top\' : offset.top, \'left\' : offset.left+p.outerWidth()-'.$icon_width.'});'."\n";
			} else {
				echo '$(\'img#colorbox_magnify\').css({\'top\' : offset.top, \'left\' : offset.left});'."\n";
			}
			echo
			'$(\'img#colorbox_magnify\').show();'."\n".
			'},function(){'."\n".
				'var p = $(this);'."\n".
				'p.removeClass(\'colorbox_hovered\');'."\n".
				'$(\'img#colorbox_magnify\').hide();'."\n".
			'});'."\n";
		}
		
		foreach (unserialize($s->colorbox_advanced) as $k => $v) {
			if ($v === '') {
				$opts[] = $k.': false';
			}
			elseif (is_bool($v)) {
				$opts[] = $k.': '.($v ? 'true' : 'false');
			}
			elseif (is_numeric($v)) {
				$opts[] = $k.": ".$v;
			}
			elseif (is_string($v)) {
				$opts[] = $k.": '".$v."'";
			}
		}
		
		echo 
		"});\n".
		'$("a[rel*=\'colorbox-\']").colorbox({'.implode(",\n",$opts).'});'."\n".
		"});\n".
		"\n//]]>\n".
		"</script>\n";
	}
}

?>