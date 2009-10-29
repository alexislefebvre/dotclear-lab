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
		$w->switchWelcome->setting('welcomesearchtext',__('Welcome text if search exists (use %1$s for keywords and %2$s for related posts\'s list):'),__('Do you search anything about %1$s? Check those related posts : %2$s'),'textarea');
		$w->switchWelcome->setting('homeonly',__('Home page only'),true,'check');
	}
	
	public static function widget($w)
	{
		global $core;

		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}
		
		if (switchWelcome::getHostReferer() === switchWelcome::getHostReferer($core->blog->url)) {
			return;
		}

		$title = strlen($w->title) > 0 ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '';
		
		$visitor = isset($_COOKIE['comment_info']) ? preg_split('#\n#',$_COOKIE['comment_info']) : array(__('visitor'));

		$res = '<p>'.sprintf($w->welcometext,$visitor[0],switchWelcome::getHostRefererLink()).'</p>';
		
		if (count(switchWelcome::getSearchWords()) > 0) {
			$res .= '<p>'.sprintf($w->welcomesearchtext,switchWelcome::getSearchWordsList(),switchWelcome::getRelatedPosts()).'</p>';
		}

		return
			'<div id="switchwelcome">'.
			$title.
			$res.
			'</div>';
	}
}

?>