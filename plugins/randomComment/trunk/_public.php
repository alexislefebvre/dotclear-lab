<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of randomComment, a plugin for Dotclear.
# 
# Copyright (c) 2009-2015 Tomtom and contributors
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

class randomCommentUrl extends dcUrlHandlers
{
	/**
	 * Includes the file to reload the widget
	 *
	 * @return	string
	 */
	public static function randomComment()
	{
		require dirname(__FILE__).'/inc/_request.php';
	}
}

class randomCommentPublic
{
	/**
	 * Return the public widget
	 *
	 * @param	objet	w
	 *
	 * @return	string
	 */
	public static function widget($w)
	{
		global $core;

		if ($w->offline)
			return;

		if (($w->homeonly == 1 && $core->url->type != 'default') ||
			($w->homeonly == 2 && $core->url->type == 'default')) {
			return;
		}

		$res =
		($w->title ? $w->renderTitle(html::escapeHTML($w->title)) : '').
		'<script type="text/javascript">'.
			'var random_comment_url = \''.$core->blog->url.$core->url->getBase('randomComment').'\';'.
			'var random_comment_ttl = '.($w->ttl*1000).';'.
			'</script>'.
			'<script type="text/javascript" src="'.$core->blog->getQmarkURL().'pf='.basename(dirname(__FILE__)).'/js/randomcomment.js"></script>'.
			'<ul id="rd_content">'.
			'</ul>';

		return $w->renderDiv($w->content_only,'randomcomment '.$w->class,'',$res);
	}
}