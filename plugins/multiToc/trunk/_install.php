<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of multiToc, a plugin for Dotclear.
# 
# Copyright (c) 2009-2015 Tomtom and contributors
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$m_version = $core->plugins->moduleInfo('multiToc','version');
$i_version = $core->getVersion('multiToc');
if (version_compare($i_version,$m_version,'>=')) {
	return;
}

# Création du setting
$settings = array(
	'cat' => array(
		'enable' => '',
		'order_group' => '',
		'display_nb_entry' => '',
		'order_entry' => '',
		'display_date' => '',
		'format_date' => $core->blog->settings->system->date_format,
		'display_author' => '',
		'display_cat' => '',
		'display_nb_com' => '',
		'display_nb_tb' => '',
		'display_tag' => ''
	),
	'tag' => array(
		'enable' => '',
		'order_group' => '',
		'display_nb_entry' => '',
		'order_entry' => '',
		'display_date' => '',
		'format_date' => $core->blog->settings->system->date_format,
		'display_author' => '',
		'display_cat' => '',
		'display_nb_com' => '',
		'display_nb_tb' => '',
		'display_tag' => ''
	),
	'alpha' => array(
		'enable' => '',
		'order_group' => '',
		'display_nb_entry' => '',
		'order_entry' => '',
		'display_date' => '',
		'format_date' => $core->blog->settings->system->date_format,
		'display_author' => '',
		'display_cat' => '',
		'display_nb_com' => '',
		'display_nb_tb' => '',
		'display_tag' => ''
	),
	'post' => array(
		'enable' => '',
		'numbering' => ''
	)
);
$core->blog->settings->addNamespace('multiToc');
$core->blog->settings->multiToc->put('multitoc_settings',serialize($settings),'string','multiToc settings',false,true);

$core->setVersion('multiToc',$m_version);

return true;