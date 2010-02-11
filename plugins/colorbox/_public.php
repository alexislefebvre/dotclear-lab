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
	public static function publicHeadContent(&$core)
	{
		if (!$core->blog->settings->colorbox_enabled) {
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
		'@import url('.$url.'/themes/'.$core->blog->settings->colorbox_theme.'/colorbox_theme.css);'."\n".
		"</style>\n".
		
		
		'<script type="text/javascript" src="'.$url.'/js/jquery.colorbox-min.js"></script>'."\n".
		
		'<script type="text/javascript">'."\n".
		"//<![CDATA[\n";
		
		echo
		'$(window).load(function(){'.
			
			
			'$("div.post").each(function() {'."\n".
				'$(this).find("a[href$=.jpg],a[href$=.jpeg],a[href$=.png],a[href$=.gif],'.
				'a[href$=.JPG],a[href$=.JPEG],a[href$=.PNG],a[href$=.GIF]").addClass("colorbox_zoom");'."\n".
				'$(this).find("a[href$=.jpg],a[href$=.jpeg],a[href$=.png],a[href$=.gif],'.
				'a[href$=.JPG],a[href$=.JPEG],a[href$=.PNG],a[href$=.GIF]").attr("rel", "colorbox");'."\n";
		
		
				
		if ($core->blog->settings->colorbox_zoom_icon_permanent) {
			
		echo	'$(this).find("a.colorbox_zoom").each(function(){'."\n".
					'var p = $(this).find("img");'."\n".
					'if (p.length != 0){'."\n".
						'var offset = p.offset();'."\n".
						'var parent = p.offsetParent();'."\n".
						'var offsetparent = parent.offset();'."\n".
						'var parenttop = offsetparent.top;'."\n".
						'var parentleft = offsetparent.left;'."\n".
						'var top = offset.top-parenttop;'."\n";
						
			if ($core->blog->settings->colorbox_position) {
				echo 	'var left = offset.left-parentleft;'."\n";
			} else {		
				echo 	'var left = offset.left-parentleft+p.outerWidth()-'.$icon_width.';'."\n";
			}	
				echo 	'$(this).append("<span style=\"z-index:10;width:'.$icon_width.'px;height:'.$icon_height.'px;top:'.'"+top+"'.'px;left:'.'"+left+"'.'px;background: url('.html::escapeJS($url).'/themes/'.$core->blog->settings->colorbox_theme.'/images/zoom.png) top left no-repeat; position:absolute;\"></span>");'."\n".
					'}'."\n".
				'});'."\n";	
					
		}						
		
		if ($core->blog->settings->colorbox_zoom_icon && !$core->blog->settings->colorbox_zoom_icon_permanent) {
			
			echo
				'$(\'body\').prepend(\'<img id="colorbox_magnify" style="display:block;padding:0;margin:0;z-index:10;width:'.$icon_width.'px;height:'.$icon_height.'px;position:absolute;top:0;left:0;display:none;" src="'.html::escapeJS($url).'/themes/'.$core->blog->settings->colorbox_theme.'/images/zoom.png" alt=""  />\');'."\n".
				
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
				
				
			if (!$core->blog->settings->colorbox_position) {
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
		echo 
			"});\n".
			'$("a[rel=\'colorbox\']").colorbox({rel:"colorbox",current:"{current} '.__('of').' {total}",
			previous:"'.__('previous').'",next:"'.__('next').'",close:"'.__('close').'",slideshowStart:"'.__('Start slideshow').'",slideshowStop:"'.__('Stop slideshow').'"});'."\n".
			"});\n".
			"\n//]]>\n".
			"</script>\n";
	}
}
?>