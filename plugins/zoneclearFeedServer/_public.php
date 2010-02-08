<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of zoneclearFeedServer, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis, BG and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

require_once dirname(__FILE__).'/_widgets.php';

if ($core->blog->settings->zoneclearFeedServer_active)
{
	$core->addBehavior('publicBeforeDocument',array('zoneclearFeedServerPublicBehaviors','publicBeforeDocument'));
	$core->addBehavior('coreBlogGetPosts',array('zoneclearFeedServerPublicBehaviors','coreBlogGetPosts'));
}

class zoneclearFeedServerPublicBehaviors
{
	public static function coreBlogGetPosts(&$rs)
	{
		$GLOBALS['beforeZcFeedRsExt'] = $rs->extensions();
		$rs->extend('zoneclearFeedServerPosts');
	}

	public static function publicBeforeDocument(&$core)
	{
		$zc = new zoneclearFeedServer($core);
		$zc->checkFeedsUpdate();
		return;
	}
}

class zoneclearFeedServerPosts extends rsExtPost
{
	public static function zcFeed(&$rs,$info)
	{
		return dcMeta::getMetaRecord($rs->core,$rs->post_meta,'zoneclearfeed_'.$info)->meta_id;
	}

	public static function zcFeedBrother($type,$args)
	{
		if (isset($GLOBALS['beforeZcFeedRsExt'][$type])) {
			$func = $GLOBALS['beforeZcFeedRsExt'][$type];
		}
		elseif (is_callable('rsExtPostPublic',$type)) {
			$func = array('rsExtPostPublic',$type);
		}
		else {
			$func = array('rsExtPost',$type);
		}
		return call_user_func_array($func,$args);
	}

	public static function getAuthorLink(&$rs)
	{
		$author = $rs->zcFeed('author');
		$site = $rs->zcFeed('site');
		$sitename = $rs->zcFeed('sitename');

		return ($author && $sitename) ?
			$author.' (<a href="'.$site.'">'.$sitename.'</a>)' :
			self::zcFeedBrother('getAuthorLink',array(&$rs));
	}

	public static function getAuthorCN(&$rs)
	{
		$author = $rs->zcFeed('author');
		return $author ? 
			$author : 
			self::zcFeedBrother('getAuthorCN',array(&$rs));
	}

	public static function getURL(&$rs)
	{
		$url = $rs->zcFeed('url');
		$types = @unserialize($rs->core->blog->settings->zoneclearFeedServer_post_full_tpl);
		$full = is_array($types) && in_array($rs->core->url->type,$types);

		return $url && $full ? 
			zoneclearFeedServer::absoluteURL($rs->zcFeed('site'),$url) : 
			self::zcFeedBrother('getURL',array(&$rs));
	}

	public static function getContent(&$rs,$absolute_urls=false)
	{
		$url = $rs->zcFeed('url');
		$sitename = $rs->zcFeed('sitename');
		$content = self::zcFeedBrother('getContent',array(&$rs,$absolute_urls));

		if ($url && $sitename && $rs->post_type == 'post')
		{
			$types = @unserialize($rs->core->blog->settings->zoneclearFeedServer_post_full_tpl);

			if (is_array($types) && in_array($rs->core->url->type,$types))
			{
				return $content .
				'<p class="zoneclear-original"><em>'.
				sprintf(__('Original post on <a href="%s">%s</a>'),$url,$sitename).
				'</em></p>';
			}
			else
			{
				$content = context::remove_html($content);
				$content = context::cut_string($content,350);	
				$content = html::escapeHTML($content);

				return
				'<p>'.$content.'... '.
				'<em><a href="'.self::zcFeedBrother('getURL',array(&$rs)).'">'.__('Continue reading').'</a></em></p>';
			}
		}
		else
		{
			return $content;
		}
	}
}

class zoneclearFeedServerURL extends dcUrlHandlers
{

}
?>