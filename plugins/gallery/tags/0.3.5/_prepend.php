<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 Gallery plugin.
#
# Copyright (c) 2004-2008 Bruno Hondelatte, and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

global $__autoload, $core;

if (!$core->plugins->moduleExists('metadata')) return false;

require (dirname(__FILE__).'/class.dc.rs.gallery.php');
$GLOBALS['__autoload']['dcGallery'] = dirname(__FILE__).'/class.dc.gallery.php';
$GLOBALS['__autoload']['dcRsGallery'] = dirname(__FILE__).'/class.dc.rs.gallery.php';


/* URL Handlers for galleries lists, galleries and images */
if ($core->blog->settings->gallery_enabled) {
	$core->url->register('gal',$core->blog->settings->gallery_gallery_url_prefix,'^'
		.$core->blog->settings->gallery_gallery_url_prefix.'/(.+)$',array('urlGallery','gallery'));
	$core->url->register('galleries',$core->blog->settings->gallery_galleries_url_prefix,'^'
		.$core->blog->settings->gallery_galleries_url_prefix.'(.*)$',array('urlGallery','galleries'));
	$core->url->register('galitem',$core->blog->settings->gallery_image_url_prefix,'^'
		.$core->blog->settings->gallery_image_url_prefix.'/(.+)$',array('urlGallery','image'));
	$core->url->register('galtheme','gallerytheme','^gallerytheme/(.+/.+)$',array('urlGalleryProxy','galtheme'));

	$core->url->register('gallerypreview','gallerypreview','^gallerypreview/(.+)$',array('urlGallery','gallerypreview'));
	$core->url->register('imagepreview','imagepreview','^imagepreview/(.+)$',array('urlGallery','imagepreview'));
	/* RNot yes implemented
	$core->url->register('images','images','^images/(.+)$',array('urlGallery','images'));
	$core->url->register('browse','browse','^browser$',array('urlGallery','browse'));
	*/
	$core->setPostType('gal','plugin.php?p=gallery&amp;m=gal&amp;id=%d',$core->url->getBase('gal').'/%s');
	$core->setPostType('galitem','plugin.php?p=gallery&amp;m=item&amp;id=%d',$core->url->getBase('galitem').'/%s');
}
?>
