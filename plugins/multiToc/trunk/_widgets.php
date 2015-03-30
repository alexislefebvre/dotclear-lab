<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of multiToc, a plugin for Dotclear.
# 
# Copyright (c) 2009-2015 Tomtom and contributors
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
		$w->create('multiToc',__('MultiToc: table of content'),array('multiTocWidgets','widget'),
			null,
			__('Contents by category, keyword and alphabetical order'));
		$w->multiToc->setting('title',__('Title:'),__('Table of content'));
		$w->multiToc->setting('homeonly',__('Display on:'),0,'combo',
			array(
				__('All pages') => 0,
				__('Home page only') => 1,
				__('Except on home page') => 2
				)
		);
		$w->multiToc->setting('content_only',__('Content only'),0,'check');
		$w->multiToc->setting('class',__('CSS class:'),'');
		$w->multiToc->setting('offline',__('Offline'),0,'check');
	}
	
	public static function widget($w)
	{
		global $core;
		
		if ($w->offline)
		return;

		if (($w->homeonly == 1 && $core->url->type != 'default') ||
			($w->homeonly == 2 && $core->url->type == 'default')) {
			return;
		}
		
		$amask = '<a href="%1$s">%2$s</a>';
		$limask = '<li class="%1$s">%2$s</li>';
		
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
		
		if (!empty($res)) {
    $res =
		($w->title ? $w->renderTitle(html::escapeHTML($w->title)) : '').
		'<ul>'.$res.'</ul>';

		return $w->renderDiv($w->content_only,'info-blog '.$w->class,'',$res);

		}
		
	}
}