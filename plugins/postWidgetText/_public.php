<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of postWidgetText, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')){return;}

$core->blog->settings->addNamespace('postwidgettext'); require dirname(__FILE__).'/_widgets.php';class postWidgetTextPublicBehaviors{	public static function widget($w)	{		global $core, $_ctx; 				if (!$core->blog->settings->postwidgettext->postwidgettext_active		 || !$_ctx->exists('posts') 		 || !$_ctx->posts->post_id) return;				$title = (strlen($w->title) > 0) ? 				'<h2>'.html::escapeHTML($w->title).'</h2>' : null;		$content = '';				$pwt = new postWidgetText($core);		$rs = $pwt->getWidgets(array('post_id'=>$_ctx->posts->post_id));				if ('' != $rs->option_title)		{			$title = '<h2>'.$rs->option_title.'</h2>';		}		if ('' != $rs->option_content_xhtml)		{			$content = $rs->option_content_xhtml;		}		if ('' == $content && $w->excerpt)		{			$content = $_ctx->posts->post_excerpt_xhtml;		}		if ('' == $content && !$w->show)		{			return;		}		return '<div class="postwidgettext">'.$title.$content.'</div>';	}}?>