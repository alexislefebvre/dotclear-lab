<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of plugin randomComment for Dotclear 2.
# Copyright (c) 2008 Thomas Bouron.
#
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets',array('randomCommentWidgets','initWidgets'));

/**
 * Class randomCommentWidgets
 */
class randomCommentWidgets
{
	/**
	 * This function create a new randomComment widget
	 *
	 * @param	object	w
	 */
	public static function initWidgets(&$w)
	{
		$w->create('randomcomment',__('Random comment'),array('randomCommentPublic','widget'));
		$w->randomcomment->setting('title',__('Title:'),__('Random comment'));
		$w->randomcomment->setting('comment_info',__('Information text about comment:'),__('By %author% about %entry% on %date%'));
		$w->randomcomment->setting('text_size',__('Text size to display (leave blank for full text):'),'');
		$w->randomcomment->setting('ttl',__('Time to reload widget (in sec.):'),'10');
		$w->randomcomment->setting('homeonly',__('Home page only'),true,'check');
	}

}

?>
