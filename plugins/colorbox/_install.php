<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of ColorBox, a plugin for Dotclear 2.
#
# Copyright (c) 2009 Philippe Amalgame and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { exit; }
 
$m_version = $core->plugins->moduleInfo('colorbox','version');
 
$i_version = $core->getVersion('colorbox');
 
if (version_compare($i_version,$m_version,'>=')) {
	return;
}

# Settings compatibility test
if (!version_compare(DC_VERSION,'2.1.7','<=')) {
	$core->blog->settings->addNamespace('colorbox');
	$s =& $core->blog->settings->colorbox;
} else {
	$core->blog->settings->setNamespace('colorbox');
	$s =& $core->blog->settings;
}

$opts = array(
	'transition' => 'elastic',
	'speed' => '350',
	'title' => '',
	'width' => '',
	'height' => '',
	'innerWidth' => '',
	'innerHeight' => '',
	'initialWidth' => '300',
	'initialHeight' => '100',
	'maxWidth' => '',
	'maxHeight' => '',
	'scalePhotos' => true,
	'scrolling' => true,
	'iframe' => false,
	'opacity' => '0.85',
	'open' => false,
	'preloading' => true,
	'overlayClose' => true,
	'loop' => true,
	'slideshow' => false,
	'slideshowSpeed' => '2500',
	'slideshowAuto' => false,
	'slideshowStart' => __('Start slideshow'),
	'slideshowStop' => __('Stop slideshow'),
	'current' => __('{current} of {total}'),
	'previous' => __('previous'),
	'next' => __('next'),
	'close' => __('close'),
	'onOpen' => '',
	'onLoad' => '',
	'onComplete' => '',
	'onCleanup' => '',
	'onClosed' => ''
);

$s->put('colorbox_enabled',false,'boolean','Enable ColorBox plugin',false,true);
$s->put('colorbox_theme','3','integer','ColorBox theme',false,true);
$s->put('colorbox_zoom_icon',false,'boolean','Enable ColorBox zoom icon',false,true);
$s->put('colorbox_zoom_icon_permanent',false,'boolean','Enable permanent ColorBox zoom icon',false,true);
$s->put('colorbox_position',false,'boolean','ColorBox zoom icon position',false,true);
$s->put('colorbox_user_files','public','boolean','ColorBox user files',false,true);
$s->put('colorbox_selectors','','string','ColorBox selectors',false,true);
$s->put('colorbox_advanced',serialize($opts),'string','ColorBox advanced options',false,true);

$core->setVersion('colorbox',$m_version);

return true;

?>