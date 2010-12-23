<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of multiToc, a plugin for Dotclear.
# 
# Copyright (c) 2009-2010 Tomtom and contributors
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets',array('multiTocWidgets','initWidgets'));

class multiTocWidgets
{
	public static function initWidgets($w)
	{
		$w->create('multiToc',__('Table of content'),array('multiTocWidgets','widget'));
		$w->multiToc->setting('title',__('Title:'),__('Table of content'));
		$w->multiToc->setting('homeonly',__('Home page only'),true,'check');
	}
	
	public static function widget($w)
	{
		global $core;
		
		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}
		
		$amask = '<a href="%1$s">%2$s</a>';
		$limask = '<li class="%1$s">%2$s</li>';
		
		$title = (strlen($w->title) > 0) ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '';
		
		$res = '';
		
		$settings = unserialize($core->blog->settings->multiToc->multitoc_settings);
		
		if ($settings['cat']['enable']) {
			$link = sprintf($amask,$core->blog->url.$core->url->getBase('multitoc').'/cat',__('By category'));
			$res .= sprintf($limask,'toc-cat',$link);
		}
		if ($settings['tag']['enable']) {
			$link = sprintf($amask,$core->blog->url.$core->url->getBase('multitoc').'/tag',__('By tag'));
			$res .= sprintf($limask,'toc-tag',$link);
		}
		if ($settings['alpha']['enable']) {
			$link = sprintf($amask,$core->blog->url.$core->url->getBase('multitoc').'/alpha',__('By alpha order'));
			$res .= sprintf($limask,'toc-alpha',$link);
		}
		
		$res = !empty($res) ? '<ul>'.$res.'</ul>' : '';
		
		return
			'<div id="info-blog">'.
			$title.
			$res.
			'</div>';
	}
}

?>