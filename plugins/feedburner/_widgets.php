<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of plugin feedburner for Dotclear 2.
# Copyright (c) 2008 Thomas Bouron.
#
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets',array('feedburnerWidgets','initWidgets'));

/**
 * Class feedburnerWidgets
 */
class feedburnerWidgets
{
	/**
	 * This function creates a new feedburner widget
	 *
	 * @param	object	w
	 */
	public static function initWidgets(&$w)
	{
		$w->create('feedburner',__('Feedburner'),array('feedburnerPublic','widget'));
		$w->feedburner->setting('title',__('Title:'),__('RSS feed'));
		$w->feedburner->setting('text',__('Text:'),__('%readers% readers - %clics% clics'));
		$w->feedburner->setting('sign_up',__('Sign up text:'),__('Sign up now'));
		$w->feedburner->setting('feed_id',__('Feed ID:'),'');
		$w->feedburner->setting('feed_int_id',__('Int feed ID (leave blank for disable):'),'');
		$w->feedburner->setting('homeonly',__('Home page only'),1,'check');
	}

}

?>
