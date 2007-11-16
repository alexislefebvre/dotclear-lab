<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear Gallery plugin.
# Copyright (c) 2007 Bruno Hondelatte,  and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# All rights reserved.
#
# Gallery plugin for DC2 is free sofwtare; you can redistribute it and/or modify
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
global $__autoload, $core;

if (!$core->plugins->moduleExists('metadata')) return false;

require (dirname(__FILE__).'/class.dc.rs.gallery.php');
$GLOBALS['__autoload']['dcGallery'] = dirname(__FILE__).'/class.dc.gallery.php';
$GLOBALS['__autoload']['dcRsGallery'] = dirname(__FILE__).'/class.dc.rs.gallery.php';

/* URL Handlers for galleries lists, galleries and images */
$GLOBALS['core']->url->register('gal',$core->blog->settings->gallery_gallery_url_prefix,'^'
	.$core->blog->settings->gallery_gallery_url_prefix.'/(.+)$',array('urlGallery','gallery'));
$GLOBALS['core']->url->register('galleries',$core->blog->settings->gallery_galleries_url_prefix,'^'
	.$core->blog->settings->gallery_galleries_url_prefix.'(.*)$',array('urlGallery','galleries'));
$GLOBALS['core']->url->register('galitem',$core->blog->settings->gallery_image_url_prefix,'^'
	.$core->blog->settings->gallery_image_url_prefix.'/(.+)$',array('urlGallery','image'));
$GLOBALS['core']->url->register('images','images','^images/(.+)$',array('urlGallery','images'));
$GLOBALS['core']->url->register('browse','browse','^browser$',array('urlGallery','browse'));


?>
