<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of switchWelcome, a plugin for Dotclear.
#
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets',array('switchWelcomeWidgets','initWidgets'));

class switchWelcomeWidgets
{
	public static function initWidgets($w)
	{
		$w->create('switchWelcome',__('Custom welcome'),array('switchWelcomeWidgets','widget'));
		$w->switchWelcome->setting('title',__('Title:'),__('Welcome!'),'text');
		$w->switchWelcome->setting('welcometext',__('Welcome text (use %1$s for visitor\'s name and %2$s for where he comes from):'),__('Hi %1$s from %2$s! Welcome on this blog!'),'textarea');
		$w->switchWelcome->setting('welcomesearchtext',__('Welcome text if search exists (use %1$s for keywords and %2$s for related posts\'s list):'),__('Do you search anything about %1$s? Check those related posts: %2$s'),'textarea');
		$w->switchWelcome->setting('homeonly',__('Display on:'),0,'combo',
			array(
				__('All pages') => 0,
				__('Home page only') => 1,
				__('Except on home page') => 2
				)
		);
		$w->switchWelcome->setting('content_only',__('Content only'),0,'check');
		$w->switchWelcome->setting('class',__('CSS class:'),'');
		$w->switchWelcome->setting('offline',__('Offline'),0,'check');
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

		if (switchWelcome::getHostReferer() === switchWelcome::getHostReferer($core->blog->url)) {
			return;
		}

		$visitor = isset($_COOKIE['comment_info']) ? preg_split('#\n#',$_COOKIE['comment_info']) : array(__('visitor'));

		$res = '<li>'.sprintf($w->welcometext,$visitor[0],switchWelcome::getHostRefererLink()).'</li>';

		if (count(switchWelcome::getSearchWords()) > 0) {
			$res .= '<li>'.sprintf($w->welcomesearchtext,switchWelcome::getSearchWordsList(),switchWelcome::getRelatedPosts()).'</li>';
		}

		$res =
		($w->title ? $w->renderTitle(html::escapeHTML($w->title)) : '').
		'<ul>'.
			$res.
		'</ul>';

		return $w->renderDiv($w->content_only,'switchwelcome '.$w->class,'',$res);
	}
}