<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of randomComment, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$core->tpl->addValue('randomCommentContent',array('randomCommentTpl','randomCommentContent'));

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

class randomCommentTpl
{
	/**
	 * Returns a random comment without http cache
	 *
	 * @return	string
	 */
	public static function randomCommentContent()
	{
		global $core, $w;

		$rd = new randomComment($core,$w);
		$rd->getRandomComment();

		return
			'<p id="rd_text">'.$rd->getWidgetContent().'</p>'.
			'<p id="rd_info">'.$rd->getWidgetInfo().'</p>';
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
	function widget(&$w)
	{
		global $core;

		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}

		$title = strlen($w->title) > 0 ? '<h2>'.$w->title.'</h2>' : '';

		$res =
			'<script type="text/javascript">'.
			'var random_comment_url = \''.$core->blog->url.$core->url->getBase('randomComment').'\';'.
			'var random_comment_ttl = '.($w->ttl*1000).';'.
			'</script>'.
			'<script type="text/javascript" src="'.$core->blog->url.'pf='.basename(dirname(__FILE__)).'/js/randomcomment.min.js"></script>'.
			'<div id="randomcomment">'.
			$title.
			'<div id="rd_content">'.
			'{{tlp:randomCommentContent}}'.
			'</div>'.
			'</div>';

		return $res;
	}
}

?>