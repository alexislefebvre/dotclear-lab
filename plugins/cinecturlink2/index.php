<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of cinecturlink2, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

dcPage::check('content');

# Init
$core->blog->settings->addNamespace('cinecturlink2');
$C2 = new cinecturlink2($core);
$root = DC_ROOT.'/'.$core->blog->settings->system->public_path;
$message = '<p class="message">%s</p>';

# Request values
$msg = isset($_REQUEST['msg']) ? $_REQUEST['msg'] : '';
$start_part = $core->blog->settings->cinecturlink2->cinecturlink2_active ? 'main' : 'setting';
$default_part = isset($_REQUEST['part']) ? $_REQUEST['part'] : $start_part;
$default_tab = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : 'links';
$section = isset($_REQUEST['section']) ? $_REQUEST['section'] : '';
$action = isset($_POST['action']) ? $_POST['action'] : '';

# Common page menu
$menu = 
'<h2>'.html::escapeHTML($core->blog->name).
' &rsaquo; <a href="'.$p_url.'&amp;part=main">'.__('Cinecturlink 2').'</a>'.
'</h2><hr class="clear" />';

# Common page footer
$footer = '<hr class="clear"/><p class="right">
<a class="button" href="'.$p_url.'&amp;part=setting">'.__('Settings').'</a> - 
cinecturlink2 - '.$core->plugins->moduleInfo('cinecturlink2','version').'&nbsp;
<img alt="'.__('cinecturlink2').'" src="index.php?pf=cinecturlink2/icon.png" />
</p>';

# Errors messages
$messages_errors = array(
	'save_setting' => __('Failed to update settings: %s'),
	'update_categories' => __('Failed to update categories: %s'),
	'order_categories' => __('Failed to reorder categories: %s'),
	'delete_categories' => __('Failed to delete categories: %s'),
	'delete_links' => __('Failed to delete links: %s'),
	'change_links_category' => __('Failed to change links category: %s'),
	'change_links_note' => __('Failed to change links note: %s'),
	'create_link' => __('Failed to create link: %s'),
	'update_link' => __('Failed to update link: %s')
);

# Pages
if (!file_exists(dirname(__FILE__).'/inc/index.'.$default_part.'.php'))
{
	$default_part = $start_part;
}
define('DC_CONTEXT_CINECTURLINK',$default_part);
include dirname(__FILE__).'/inc/index.'.$default_part.'.php';
?>