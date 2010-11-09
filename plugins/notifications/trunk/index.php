<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of notifications, a plugin for Dotclear.
# 
# Copyright (c) 2009-2010 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

# Initialization
$nb_per_page	= 20;
$page		= isset($_GET['page']) ? $_GET['page'] : '1';
$p_url 		= 'plugin.php?p=notifications';
$default_tab	= isset($_GET['tab']) ? $_GET['tab'] : 'components';
$component	= isset($_GET['set']) ? $_GET['set'] : null;

# Save plugin components
if (isset($_POST['savecomponents'])) {
	$pids = $sids = array();
	foreach ($_POST['ids'] as $k => $v) {
		$pids[$v] = true;
	}
	try {
		$sids = unserialize($core->blog->settings->notifications->disabled_components);
		foreach ($pids as $id => $v) {
			if ($_POST['action'] === 'disable') {
				$sids[$id] = true;
			}
			else {
				unset($sids[$id]);
			}
		}
		$core->blog->settings->notifications->put('disabled_components',serialize($sids));
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
	http::redirect($p_url.'&upd=1');
}
# Save plugin config
if (isset($_POST['savepluginconfig'])) {
	try {
		$core->blog->settings->notifications->put('enable',html::escapeHTML($_POST['enable']));
		$core->blog->settings->notifications->put('sticky',html::escapeHTML($_POST['sticky']));
		$core->blog->settings->notifications->put('display_all',html::escapeHTML($_POST['display_all']));
		$core->blog->settings->notifications->put('position',$_POST['position']);
		$core->blog->settings->notifications->put('display_time',$_POST['display_time']);
		$core->blog->settings->notifications->put('refresh_time',$_POST['refresh_time']);
		$core->blog->settings->notifications->put('auto_clean',$_POST['auto_clean']);
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
	http::redirect($p_url.'&tab=config&upd=2');
}
# Save component config
if (isset($_POST['savecomponentconfig'])) {
	try {
		$perms = unserialize($core->blog->settings->notifications->permissions_types);
		$perms[$_POST['component']]['new'] = $_POST['new'];
		$perms[$_POST['component']]['upd'] = $_POST['upd'];
		$perms[$_POST['component']]['del'] = $_POST['del'];
		$perms[$_POST['component']]['msg'] = $_POST['msg'];
		$perms[$_POST['component']]['err'] = $_POST['err'];
		$perms[$_POST['component']]['spm'] = $_POST['new'];
		$core->blog->settings->notifications->put('permissions_types',serialize($perms));
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
	http::redirect($p_url.sprintf('&set=%s&upd=3',html::escapeHTML($_POST['component'])));
}

$combo_data = array(
	__('Top - Left') => 'top-left',
	__('Top - Right') => 'top-right',
	__('Bottom - Left') => 'bottom-left',
	__('Bottom - Right') => 'bottom-right',
	__('Center') => 'center'
);

$notifications = new notifications($core);
$c_rs = $notifications->getComponents();
$c_nb = count($c_rs);
$c_rs = staticRecord::newFromArray($c_rs);
$c_list = new notificationsList($core,$c_rs,$c_nb);

# DISPLAY
# -------
echo
'<html>'.
'<head>'.
	'<title>'.__('Notifications').'</title>'.
	dcPage::jsModal().
	(is_null($component) ? dcPage::jsPageTabs($default_tab) : '').
	dcPage::jsLoad('index.php?pf=notifications/js/_notifications.js').
'</head>'.
'<body>';

# General messages
if (isset($_GET['upd'])) {
	if ($_GET['upd'] === '1') {
		echo '<p class="message">'.__('Components have been successfully updated').'</p>';
	}
	if ($_GET['upd'] === '2') {
		echo '<p class="message">'.__('Configuration has been successfully updated').'</p>';
	}
	if ($_GET['upd'] === '3') {
		echo '<p class="message">'.__('Component permissions have been successfully updated').'</p>';
	}
}

# Title
echo
'<h2>'.$core->blog->name.' &rsaquo; '.
(!is_null($component) ? '<a href="'.$p_url.'">'.__('Notifications').'</a>' : __('Notifications')).
(!is_null($component) ? ' &rsaquo; '.__('Component permissions') : '').
'</h2>';

# Tabs for components and config
if (is_null($component)) {
	echo
	'<!-- Appplications -->'.
	'<div class="multi-part" id="components" title="'.__('Registered components').'">';
	$c_list->display($page,$nb_per_page,$p_url);
	echo
	'</div>'.
	'<!-- Configuration -->'.
	'<div class="multi-part" id="config" title="'.__('Configuration').'">'.
	'<form method="post" action="'.$p_url.'">'.
		'<p class="field">'.
			form::checkbox('enable',1,$core->blog->settings->notifications->enable).
			'<label class="classic" for="enable">'.__('Enable notifications').'</label>'.
		'</p>'.
		'<p class="field">'.
			form::checkbox('sticky',1,$core->blog->settings->notifications->sticky).
			'<label class="classic" for="sticky">'.__('Sticky notifications').'</label>'.
		'</p>'.
		'<p class="field">'.
			form::checkbox('display_all',1,$core->blog->settings->notifications->display_all).
			'<label class="classic" for="display_all">'.__('Display all notifications of all blogs').'</label>'.
		'</p>'.
		'<p class="field">'.
			form::combo('position',$combo_data,$core->blog->settings->notifications->position).
			'<label class="classic" for="position">'.__('Position of notifications').'</label>'.
		'</p>'.
		'<p class="field">'.
			form::field('display_time',30,255,$core->blog->settings->notifications->display_time).
			'<label class="classic" for="display_time">'.__('Time to display notifications (second)').'</label>'.
		'</p>'.
		'<p class="field">'.
			form::field('refresh_time',30,255,$core->blog->settings->notifications->refresh_time).
			'<label class="classic" for="refresh_time">'.__('Time beetween each request (second)').'</label>'.
		'</p>'.
		'<p class="field">'.
			form::checkbox('auto_clean',1,$core->blog->settings->notifications->auto_clean).
			'<label class="classic" for="auto_clean">'.__('Auto clean notifications').'</label>'.
		'</p>'.
		'<p>'.
		$core->formNonce().
		'<input type="submit" name="savepluginconfig" value="'.__('Save configuration').'" />'.
		'</p>'.
	'</form>'.
	'</div>';
}

# Component config page
else {
	echo	
	'<fieldset><legend>'.sprintf(__('Permissions for component: %s'),$component).'</legend>'.
	'<form method="post" action="'.$p_url.'">';
	
	foreach ($notifications->getPermissionsTypes($component) as $id => $perm)
	{
		echo
		'<p class="field">'.
			form::combo($id,array_flip($core->auth->getPermissionsTypes()),$perm).
			'<label class="classic" for="position">'.
			sprintf(__('Required permission for type %s:'),'<q>'.$id.'</q>').
			'</label>'.
		'</p>';
	}
	
	echo
	'<p><input type="hidden" name="component" value="'.$component.'" />'.
	$core->formNonce().
	'<input type="submit" name="savecomponentconfig" value="'.__('Save permissions').'" />'.
	'</p>'.
	'</form>'.
	'</fieldset>';
}

echo
'</body>'.
'</html>';