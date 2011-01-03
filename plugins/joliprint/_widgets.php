<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of joliprint, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

$core->addBehavior('initWidgets',array('joliprintWidget','joliprintAdmin'));

class joliprintWidget
{
	public static function joliprintAdmin($w)
	{
		$w->create('joliprint',
			__('Joliprint'),array('joliprintWidget','joliprintPublic')
		);
		$w->joliprint->setting('title',
			__('Title:'),'','text'
		);
		$w->joliprint->setting('button',
			__('Picture:'),'joliprint-button.jpg','combo',joliprint::buttons()
		);
		$w->joliprint->setting('text',
			__('Text:'),'','text'
		);
		$w->joliprint->setting('server',
			__('Country:'),'eu.joliprint.com','combo',joliprint::servers()
		);
	}
	
	public static function joliprintPublic($w)
	{
		global $core, $_ctx;
		
		$params = array();
		$s = $core->blog->settings->joliprint;
		if (!$s->active) { return; }
		
		if (!$_ctx->exists('posts')) { return; }
		
		$params['url'] = $s->btn_cleanpost ?
			$core->blog->url.$core->url->getBase('joliprint').'/'.$core->getPostPublicURL($_ctx->posts->post_type,html::sanitizeURL($_ctx->posts->post_url)) :
			$_ctx->posts->getURL();
		
		$params['button'] = $w->button;
		$params['text'] = $w->text;
		$params['server'] = $w->server;
	
		return 
		'<div class="widgetjoliprint">'.
		($w->title ? '<h3>'.$w->title.'</h3>' : '').
		joliprint::toHTML($params).
		'</div>';
	}
}
?>