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
if (!defined('DC_CONTEXT_ADMIN')) exit;

$this_version = $core->plugins->moduleInfo('gallery','version');
$installed_version = $core->getVersion('gallery');
 
if (version_compare($installed_version,$this_version,'>=') && ($core->blog->settings->gallery_gallery_url_prefix !==null)) {
	return;
}

function defaultIfNotSet($value,$default) {
	if ($value === null)
		return $default;
	else
		return $value;
}


$galleries_url_prefix = defaultIfNotSet($core->blog->settings->gallery_galleries_url_prefix,'galleries');
$gallery_url_prefix = defaultIfNotSet($core->blog->settings->gallery_gallery_url_prefix,'gallery');
$image_url_prefix = defaultIfNotSet($core->blog->settings->gallery_image_url_prefix,'image');
$images_url_prefix = defaultIfNotSet($core->blog->settings->gallery_images_url_prefix,'images');
$browser_url_prefix = defaultIfNotSet($core->blog->settings->gallery_browser_url_prefix,'browser');
$default_theme = defaultIfNotSet($core->blog->settings->gallery_default_theme,'default');
$nb_images_per_page = defaultIfNotSet($core->blog->settings->gallery_nb_images_per_page,24);

$core->blog->settings->setNamespace('gallery');
$core->blog->settings->put('gallery_galleries_url_prefix',$galleries_url_prefix,'string','Gallery lists URL prefix');
$core->blog->settings->put('gallery_gallery_url_prefix',$gallery_url_prefix,'string','Galleries URL prefix');
$core->blog->settings->put('gallery_image_url_prefix',$image_url_prefix,'string','Images URL prefix');
$core->blog->settings->put('gallery_images_url_prefix',$images_url_prefix,'string','Filtered Images URL prefix');
$core->blog->settings->put('gallery_browser_url_prefix',$browser_url_prefix,'string','Browser URL prefix');
$core->blog->settings->put('gallery_default_theme',$default_theme,'string','Default theme to use');
$core->blog->settings->put('gallery_nb_images_per_page',$nb_images_per_page,'integer','Number of images per page');
$core->setVersion('gallery',$this_version);

return true;
?>
