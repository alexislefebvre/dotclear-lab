<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of postWidgetText a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

$core->addBehavior('initWidgets',
	array('postWidgetTextWidget','initWidget'));

class postWidgetTextWidget
{
	public static function initWidget($w)
	{
		global $core;

		$w->create('postwidgettext',__('Post widget text'),
			array('postWidgetTextWidget','parseWidget'));
		$w->postwidgettext->setting('title',__('Title:'),
			__('More about this entry'),'text');
		$w->postwidgettext->setting('excerpt',__('Use excerpt if no content'),
			0,'check');
		$w->postwidgettext->setting('show',__('Show widget even if empty'),
			0,'check');
	}

	public static function parseWidget($w)
	{
		global $core, $_ctx; 

		if (!$core->blog->settings->postwidgettext_active
		 || 'post.html' != $_ctx->current_tpl 
		 || !$_ctx->posts->post_id) return;

		$header = (strlen($w->title) > 0) ? 
			'<h2>'.html::escapeHTML($w->title).'</h2>' : null;
		$content = '';

		$postWidgetText = new postWidgetText($core);
		$rs = $postWidgetText->get($_ctx->posts->post_id,'postwidgettext');

		if (empty($rs->wtext_content_xhtml))
			$content = $rs->wtext_content_xhtml;
		if (empty($content) && $w->excerpt)
			$content = $_ctx->posts->post_excerpt_xhtml;
		if (empty($content) && !$w->show)
			return;

		return '<div class="postwidgettext">'.$header.$content.'</div>';
	}
}
?>