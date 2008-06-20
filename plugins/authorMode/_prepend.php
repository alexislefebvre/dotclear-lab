<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 2.
#
# Copyright (c) 2003-2008 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
$core =& $GLOBALS['core'];

class rsAuthor
{ 
	public static function getAuthorCN(&$rs)
	{
		return dcUtils::getUserCN($rs->user_id, $rs->user_name,
		$rs->user_firstname, $rs->user_displayname);
	}
	
	public static function getAuthorLink(&$rs)
	{
		$res = '%1$s';
		$url = $rs->user_url;
		if ($url) {
			$res = '<a href="%2$s">%1$s</a>';
		}
		
		return sprintf($res,$rs->getAuthorCN(),$url);
	}
	
	public static function getAuthorEmail(&$rs,$encoded=true)
	{
		if ($encoded) {
			return strtr($rs->user_email,array('@'=>'%40','.'=>'%2e'));
		}
		return $rs->user_email;
	}
}	

if ($core->blog->settings->authormode_active) 
{
	if ($core->blog->settings->authormode_url_author !== null) {
		$url_prefix = $core->blog->settings->authormode_url_author;
		if (empty($url_prefix)) {
			$url_prefix = 'author';
		}
		$feed_prefix = $core->url->getBase('feed').'/'.$url_prefix;
		$core->url->register('author',$url_prefix,'^'.$url_prefix.'/(.+)$',array('urlAuthor','author'));
		$core->url->register('author_feed',$feed_prefix,'^'.$feed_prefix.'/(.+)$',array('urlAuthor','feed'));
		unset($url_prefix,$feed_prefix);
	}
	
	if ($core->blog->settings->authormode_url_authors !== null) {
		$url_prefix = $core->blog->settings->authormode_url_authors;
		if (empty($url_prefix)) {
			$url_prefix = 'authors';
		}
		$core->url->register('authors',$url_prefix,'^'.$url_prefix.'$',array('urlAuthor','authors'));
		unset($url_prefix);
	}
}
?>