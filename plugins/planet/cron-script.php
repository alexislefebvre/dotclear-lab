#!/usr/bin/env php
<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2008 Olivier Meunier and contributors. All rights
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
$opts = getopt('d:c:b:u:h');

function help($status=0)
{
	echo
	"Options: \n".
	" -h shows this help\n".
	" -d DotClear root path\n".
	" -c DotClear conf path\n".
	" -b Blog ID\n".
	" -u User ID\n\n";
	exit($status);
}

if (isset($opts['h'])) {
	help();
}

$dc_root = null;
$dc_conf = null;
$blog_id = null;

if (isset($opts['d'])) {
	$dc_root = $opts['d'];
} elseif (isset($_SERVER['DC_ROOT'])) {
	$dc_root = $_SERVER['DC_ROOT'];
}

if (isset($opts['c'])) {
	$dc_conf = realpath($opts['c']);
} elseif (isset($_SERVER['DC_RC_PATH'])) {
	$dc_conf = realpath($_SERVER['DC_RC_PATH']);
}

if (isset($opts['b'])) {
	$blog_id = $opts['b'];
} elseif (isset($_SERVER['DC_BLOG_ID'])) {
	$blog_id = $opts['DC_BLOG_ID'];
}

if (!$dc_root || !is_dir($dc_root)) {
	fwrite(STDERR,"DotClear root path is not defined\n\n");
	help(1);
}

if (!$dc_conf || !is_readable($dc_conf)) {
	fwrite(STDERR,"DotClear configuration not found\n\n");
	help(1);
}

if (!$blog_id) {
	fwrite(STDERR,"Blog ID is not defined\n\n");
	help(1);
}

$_SERVER['DC_RC_PATH'] = $dc_conf;
unset($dc_conf);

define('DC_BLOG_ID',$blog_id);
unset($blog_id);

require $dc_root.'/inc/prepend.php';
unset($dc_root);

$core->setBlog(DC_BLOG_ID);
if ($core->blog->id == null) {
	fwrite(STDERR,"Blog is not defined\n");
	exit(1);
}

if (!isset($opts['u']) || !$core->auth->checkUser($opts['u'])) {
	fwrite(STDERR,"Unable to set user\n");
	exit(1);
}

$core->plugins->loadModules(DC_PLUGINS_ROOT);

try {
	require dirname(__FILE__).'/insert_feeds.php';
} catch (Exception $e) {
	fwrite(STDERR,$e->getMessage()."\n");
	exit(1);
}
?>