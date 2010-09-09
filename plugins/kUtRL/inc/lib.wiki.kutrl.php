<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of kUtRL, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

# This file contents class to shorten url pass through wiki

if (!defined('DC_RC_PATH')){return;}

class kutrlWiki
{
	public static function coreInitWiki($wiki2xhtml)
	{
		global $core;
		$s = $core->blog->settings->kUtRL;

		# Do nothing on comment preview and post preview
		if (!empty($_POST['preview']) 
		 || !empty($GLOBALS['_ctx']) && $GLOBALS['_ctx']->preview) return;
		
		if (!$s->kutrl_active || !$s->kutrl_wiki_service 
		 || !isset($core->kutrlServices[$s->kutrl_wiki_service])) return;
		
		try
		{
			$kut = new $core->kutrlServices[$s->kutrl_wiki_service]($core,$s->kutrl_limit_to_blog);
		}
		catch (Exception $e)
		{
			return;
		}
		
		foreach($kut->allowed_protocols as $protocol)
		{
			$wiki2xhtml->registerFunction(
				'url:'.$protocol,
				array('kutrlWiki','transform')
			);
		}
	}
	
	public static function transform($url,$content)
	{
		global $core;
		$s = $core->blog->settings->kUtRL;
		
		if (!$s->kutrl_active || !$s->kutrl_wiki_service 
		 || !isset($core->kutrlServices[$s->kutrl_wiki_service])) return;
		
		try
		{
			$kut = new $core->kutrlServices[$s->kutrl_wiki_service]($core,$s->kutrl_limit_to_blog);
		}
		catch (Exception $e)
		{
			return array();
		}
		
		# Test if long url exists
		$is_new = false;
		$rs = $kut->isKnowUrl($url);
		if (!$rs)
		{
			$is_new = true;
			$rs = $kut->hash($url);
		}

		if (!$rs)
		{
			return array();
		}
		else
		{
			$res = array();
			$testurl = strlen($rs->url) > 35 ? substr($rs->url,0,35).'...' : $rs->url;
			$res['url'] = $kut->url_base.$rs->hash;
			$res['title'] = sprintf(__('%s (Shorten with %s)'),$rs->url,__($kut->name));
			if ($testurl == $content) $res['content'] = $res['url'];
			
			# Send new url to messengers
			if ($s->kutrl_twit_onwiki && $s->kutrl_twit_msg && $is_new)
			{
				$user = !defined('DC_CONTEXT_ADMIN') ? __('public') : $core->auth->getInfo('user_cn');
				$twit = str_replace(array('%L','%B','%U'),array($res['url'],$core->blog->name,$user),$s->kutrl_twit_msg);
				kutrlSendToMessengers($core,$twit);
			}
			
			return $res;
		}
	}
}
?>