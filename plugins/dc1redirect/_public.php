<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2.
#
# Copyright (c) 2006-2008 Pep and contributors. All rights
# reserved. Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

if (!$core->blog->settings->dc1_redirect) {
	return;
}

$core->url->register('redir_post','','^(\d{4}/\d{2}/\d{2}/\d+.+)$',array('dcUrlRedirect','redir_post'));
$core->url->register('redir_category','','^([A-Z]+[A-Za-z0-9_-]*)/?$',array('dcUrlRedirect','redir_category'));

if ($core->plugins->moduleExists('dayMode') && $core->blog->settings->daymode_active) {
	$archive_pattern = '^(\d{4}/\d{2}(/\d{2})?)/?$';
} else {
	$archive_pattern = '^(\d{4}/\d{2})(?:/\d{2})?/?$';
}
$core->url->register('redirect_archive','',$archive_pattern, array('dcUrlRedirect','redir_archive'));
unset($archive_pattern);

class dcUrlRedirect extends dcUrlHandlers
{
	public static function redir_post($args)
	{
		http::head(301);
		header('Location: '.self::redir_url('post',$args));
		exit;
	}
	
	public static function redir_category($args)
	{
		http::head(301);
		header('Location: '.self::redir_url('category',$args));
		exit;
	}
	
	public static function redir_archive($args)
	{
		http::head(301);
		header('Location: '.self::redir_url('archive',$args));
		exit;
	}
	
	protected static function redir_url($type,$args)
	{
		return $GLOBALS['core']->blog->url.$GLOBALS['core']->url->getBase($type).'/'.$args;
	}
}
?>