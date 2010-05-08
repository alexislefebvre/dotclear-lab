<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of twitterWidget, a plugin for Dotclear.
# 
# Copyright (c) 2008 annso and contributors
# contact@as-i-am.fr
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets',
	array('twitterWidget','initWidgets'));

class twitterWidget
{
	public static function initWidgets($w)
	{
		$w->create('Twitter',__('Twitter'),
			array('publicTwitterWidget','getTweets'));
		
		$w->Twitter->setting('title',__('Title:'),
			'Twitter','text');
		
		$w->Twitter->setting('username',__('Twitter username:'),
			'dotclear','text');
		
		$w->Twitter->setting('count',__('Number of tweets:'),
			'1','text');
		
		$w->Twitter->setting('prefix',__('Prefix:'),
			__('%name% said:'),'text');
		
		$w->Twitter->setting('ignorereplies',__('Skip over replies'),
			false,'check');
		
		$w->Twitter->setting('enablelinks',__('Linkify text'),
			true,'check');
		
		$w->Twitter->setting('template',__('HTML template:'),
			'"%text%" <span class="twitterTime">Posted <a href="http://twitter.com/%user_screen_name%/statuses/%id%/">%time%</a></span>',
			'textarea');
		
		$w->Twitter->setting('homeonly',__('Home page only'),
			false,'check');
		
	}
}
?>