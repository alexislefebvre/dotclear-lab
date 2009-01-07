<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of plugin multiToc for Dotclear 2.
# Copyright (c) 2008 Thomas Bouron and contributors.
#
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
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
		'format_date' => $core->blog->settings->date_format,
		'display_author' => '',
		'display_nb_com' => '',
		'display_nb_tb' => ''
	),
	'tag' => array(
		'enable' => '',
		'order_group' => '',
		'display_nb_entry' => '',
		'order_entry' => '',
		'display_date' => '',
		'format_date' => $core->blog->settings->date_format,
		'display_author' => '',
		'display_nb_com' => '',
		'display_nb_tb' => ''
	),
	'alpha' => array(
		'enable' => '',
		'order_group' => '',
		'display_nb_entry' => '',
		'order_entry' => '',
		'display_date' => '',
		'format_date' => $core->blog->settings->date_format,
		'display_author' => '',
		'display_nb_com' => '',
		'display_nb_tb' => ''
	)
);
$core->blog->settings->setNamespace('multiToc');
$core->blog->settings->put(
	'multitoc_settings',
	serialize($settings),
	'string','MultiToc settings',true,true
);

$core->setVersion('multiToc',$m_version);
return true;
?>