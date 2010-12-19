<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of joliprint, a plugin for Dotclear 2.
# 
# Copyright (c) 2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

# Settings NS
$core->blog->settings->addNamespace('joliprint');

# Widget
require_once dirname(__FILE__).'/_widgets.php';

# behaviors
$core->addBehavior('publicHeadContent',array('joliprintPublic','publicheadContent'));
$core->addBehavior('publicEntryBeforeContent',array('joliprintPublic','publicEntryBeforeContent'));
$core->addBehavior('publicEntryAfterContent',array('joliprintPublic','publicEntryAfterContent'));

# Template
$core->tpl->addValue('joliprintButton',array('joliprintTpl','joliprintButton'));

class joliprintPublic
{
	public static function publicHeadContent($core)
	{
		if (!$core->blog->settings->joliprint->active){return;}
		
		# CSS
		$btn_css = $core->blog->settings->joliprint->btn_css;
		if (!empty($btn_css)) {
			echo 
			"\n<!-- Style for plugin joliprint --> \n".
			'<style type="text/css">'."\n".
			html::escapeHTML($btn_css)."\n".
			"</style>\n";
		}
/* JS not yet implemented
		# JS
		echo 
		"\n<!-- JS for joliprint --> \n".
		"<script charset='ISO-8859-1' src='http://api.joliprint.com/joliprint/js/joliprint.js' type='text/javascript'></script> \n";
//*/
	}
	
	public static function publicEntryBeforeContent($core,$_ctx)
	{
		self::publicEntryContent($core,$_ctx,true);
	}
	
	public static function publicEntryAfterContent($core,$_ctx)
	{
		self::publicEntryContent($core,$_ctx,false);
	}
	
	private static function publicEntryContent($core,$_ctx,$before)
	{
		# Settings
		$s = $core->blog->settings->joliprint;
		
		# Conditions
		if (!$s->active // plugin is active
		 || !$_ctx->exists('posts') // on post
		 || $s->btn_place != 'both' 
		 && ($before && $s->btn_place != 'before' // before content
		 || !$before && $s->btn_place != 'after')) // after content
		{
			return;
		}
		
		# Pages
		$btn_pages = @unserialize($s->btn_pages);
		if (!is_array($btn_pages) || !in_array($core->url->type,$btn_pages))
		{
			return;
		}
		
		# Params
		$params = array();
		$params['url'] = $_ctx->posts->getURL();
		$params['server'] = (string) $s->btn_server;
		$params['button'] = (string) $s->btn_button;
		$params['text'] = (string) $s->btn_text;
		
		# Display
		echo  
		'<div class="postjoliprint">'.
		joliprint::toHTML($params).
		'</div>';
	}
}

class joliprintTpl
{
	public static function joliprintButton($attr)
	{
		global $core, $_ctx;

		if (!$core->blog->settings->joliprint->active) return;
		
		$res = '';
		
		# URL
		if (isset($attr['url'])) {
			$res .= "\$joliprint_params['url'] = \"".html::escapeHTML($attr['url'])."\"; \n";
		}
		elseif ($_ctx->exists('posts')) {
			$res .= "\$joliprint_params['url'] = \$_ctx->posts->getURL(); \n";
		}
		else {
			return;
		}
		# Server
		if (isset($attr['server'])) {
			$res .= "\$joliprint_params['server'] = \"".html::escapeHTML($attr['server'])."\"; \n";
		}
		# Picture
		if (isset($attr['button'])) {
			$res .= "\$joliprint_params['button'] = \"".html::escapeHTML($attr['button'])."\"; \n";
		}
		# Text
		if (isset($attr['text'])) {
			$res .= "\$joliprint_params['text'] = \"".html::escapeHTML($attr['text'])."\"; \n";
		}
		
		return 
		"<?php \n".
		"\$joliprint_params = array(); \n".
		$res.
		"echo joliprint::toHTML(\$joliprint_params); \n".
		"unset(\$joliprint_params); \n".
		"?>\n";
	}
}
?>