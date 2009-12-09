<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of kUtRL, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

class kutrlWiki
{
	public static function coreInitWiki($wiki2xhtml)
	{
		global $core;

		$_active = (boolean) $core->blog->settings->kutrl_active;
		$_wiki_service = (string) $core->blog->settings->kutrl_wiki_service;
		$_limit_to_blog = (boolean) $core->blog->settings->kutrl_limit_to_blog;

		if (!$_active || !$_wiki_service) return;

		if (!isset($core->kutrlServices[$_wiki_service])) return;

		try
		{
			$kut = new $core->kutrlServices[$_wiki_service]($core,$_limit_to_blog);
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

		$_active = (boolean) $core->blog->settings->kutrl_active;
		$_wiki_service = (string) $core->blog->settings->kutrl_wiki_service;
		$_limit_to_blog = (boolean) $core->blog->settings->kutrl_limit_to_blog;

		if (!$_active || !$_wiki_service) return;

		if (!isset($core->kutrlServices[$_wiki_service])) return;

		try
		{
			$kut = new $core->kutrlServices[$_wiki_service]($core,$_limit_to_blog);
		}
		catch (Exception $e)
		{
			return array();
		}

		$rs = $kut->hash($url);

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

			return $res;
		}
	}
}
?>