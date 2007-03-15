<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2005 Olivier Meunier and contributors. All rights
# reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****
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

if (!$core->blog->settings->authormode_active) return;

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
?>