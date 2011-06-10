<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of shareOn, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

$core->addBehavior('initWidgets',array('shareOnWidget','adminShareOn'));

class shareOnWidget
{
	public static function adminShareOn($w)
	{
		global $core;
		
		$w->create('shareon',
			__('Share on'),array('shareOnWidget','publicShareOn')
		);
		$w->shareon->setting('title',
			__('Title:'),__('Share on'),'text'
		);
		$w->shareon->setting('small',
			__('Use small buttons'),1,'check'
		);
		foreach($core->shareOnButtons as $button_id => $button)
		{
			$o = new $button($core);
			
			$w->shareon->setting('use_'.$button_id,
				sprintf(__('Add %s'),$o->name),1,'check'
			);
		}
	}
	
	public static function publicShareOn($w)
	{
		global $core, $_ctx;
		
		# Disable
		if (!$core->blog->settings->shareOn->shareOn_active) return;
		# No button
		if (empty($core->shareOnButtons)) return;
		# Not in post context
		if ('post.html' != $_ctx->current_tpl) return;
		
		$li = '';	
		foreach($core->shareOnButtons as $button_id => $button)
		{
			$n = 'use_'.$button_id;
			if (!$w->{$n}) continue;

			$o = new $button($core);
			$o->_active = true;
			$o->_small = (boolean) $w->small;
			$res = $o->generateHTMLButton($_ctx->posts->getURL(),$_ctx->posts->post_title);

			if (!empty($res)) $li .= '<li>'.$res.'</li>';
		}
		
		if (empty($li)) return;
		
		return
		'<div class="shareonwidget">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>'.$li.'</ul>'.
		'</div>';
	}
}
?>