<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of pollsFactory, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

# Check user perms
dcPage::check('admin');

# Objects
$s = $core->blog->settings;
$fact = new pollsFactory($core);

# Default values
$echo = '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

# Messages
$msg = isset($_REQUEST['msg']) ? $_REQUEST['msg'] : '';
$msg_list = array(
	'savesetting' => __('Configuration successfully saved'),
	'createpoll' => __('Poll successfully created'),
	'editperiod' => __('Poll period successfully updated'),
	'deletepoll' => __('Poll successfully deleted'),
	'deletequery' => __('Queries successfully deleted'),
	'reorderquery' => __('Queries successfully reordered'),
	'createquery' => __('Query successfully created'),
	'deletenewquery' => __('New query successfully deleted'),
	'deleteoption' => __('Options successfully deleted'),
	'createoption' => __('Option successfully created'),
	'finishquery' => __('Query successfully completed'),
	'finishpoll' => __('Poll successfully completed'),
	'selectresponses' => __('Responses succesfully selected')
);
if (isset($msg_list[$msg])) {
	$msg = sprintf('<p class="message">%s</p>',$msg_list[$msg]);
}

# Menus
$start_tab = $s->pollsFactory_active ? 'polls' : 'setting';
$default_tab = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : $start_tab;
$tabs = array(
	'setting' => __('Options'),
	'polls' => __('Polls list'),
	'addpoll' => __('Create poll'),
	'viewpoll' => __('Poll results')
);

# Page
if (!file_exists(dirname(__FILE__).'/inc/index.'.$default_tab.'.php')) {
	$default_tab = 'setting';
}
require dirname(__FILE__).'/inc/index.'.$default_tab.'.php';

if (!isset($tabs[$default_tab])) {
	$tabs[$default_tab] = __('Edit');
}


# Display
echo '
<html><head>
<title>'.__('Polls factory').'</title>'.
dcPage::jsDatePicker().
//dcPage::jsToolBar().
dcPage::jsLoad('js/_posts_list.js').
dcPage::jsLoad('js/filter-controls.js').
dcPage::jsPageTabs($default_tab).
dcPage::jsColorPicker().
dcPage::jsToolMan().
dcPage::jsLoad('index.php?pf=pollsFactory/js/admin.js').
'
</head>
<body>
<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('Polls factory').'</h2>';

$menu = '';
foreach($tabs as $k => $v)
{
	if ($default_tab == $k) {
		echo '<div class="multi-part" id="'.$k.'" title="'.$v.'">'.(!empty($msg) ? $msg : '').$echo.'</div>';
	} else {
		echo '<a href="'.$p_url.'&amp;tab='.$k.'" class="multi-part">'.$v.'</a>';
	}
}

echo 
dcPage::helpBlock('pollsFactory').'
<hr class="clear"/>
<p class="right">
pollsFactory - '.$core->plugins->moduleInfo('pollsFactory','version').'&nbsp;
<img alt="'.__('Polls factory').'" src="index.php?pf=pollsFactory/icon.png" />
</p></body></html>';
?>