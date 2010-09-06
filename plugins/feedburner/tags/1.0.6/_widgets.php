<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of feedburner, a plugin for Dotclear.
# 
# Copyright (c) 2009-2010 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets',array('feedburnerWidgets','initWidgets'));

class feedburnerWidgets
{
	/**
	 * This function creates a new feedburner widget
	 *
	 * @param	object	w
	 */
	public static function initWidgets($w)
	{
		global $core;

		$feeds = unserialize($core->blog->settings->feedburner->feedburner_feeds);
		foreach ($feeds as $k => $v) {
			if (empty($v)) { unset($feeds[$k]); }
		}

		$w->create('feedburner',__('Feedburner'),array('feedburnerPublic','widget'));
		$w->feedburner->setting('title',__('Title:'),__('RSS feed'));
		$w->feedburner->setting('text',__('Text:'),__('%readers% readers - %clics% clics'));
		$w->feedburner->setting('sign_up',__('Sign up text:'),__('Sign up now'));
		$w->feedburner->setting('feed_id',__('Feed:'),null,'combo',$feeds);
		$w->feedburner->setting('email',__('Display link for feed email subscription:'),0,'check');
		$w->feedburner->setting('homeonly',__('Home page only'),0,'check');
	}

}

?>