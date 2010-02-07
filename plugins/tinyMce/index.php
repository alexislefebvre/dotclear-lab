<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of TinyMCE, a plugin for Dotclear 2
# Copyright 2010 Moe (http://gniark.net/)
#
# TinyMCE is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# TinyMCE is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public
# License along with this program. If not, see
# <http://www.gnu.org/licenses/>.
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) {return;}

if (isset($_GET['tinyMce_file']))
{
	$f = dirname(__FILE__).'/js/tiny_mce_jquery/'.$_GET['tinyMce_file'];
	
	if (!is_file($f) || !is_readable($f)) {
		header('Content-Type: text/plain');
		http::head(404,'Not Found');
		exit;
	}
	http::$cache_max_age = 7200;
	http::cache(array_merge(array($f),get_included_files()));

	header('Content-Type: '.files::getMimeType($f));
	header('Content-Length: '.filesize($f));
	readfile($f);
}
else
{
	if (isset($_GET['type']) && ($_GET['type'] == 'page'))
	{
		require_once(dirname(__FILE__).'/admin/page.php');
	}
	else
	{
		require_once(dirname(__FILE__).'/admin/post.php');
	}	
}

exit;

?>