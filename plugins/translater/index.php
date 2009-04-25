<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of "translater" a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) return;

# Load translations of alert messages
$_lang = $core->auth->getInfo('user_lang');
$_lang = preg_match('/^[a-z]{2}(-[a-z]{2})?$/',$_lang) ? $_lang : 'en';
l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/error');
l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/admin');

# Load class
$O = new dcTranslater($core);

# Init some vars
$p_url 	= 'plugin.php?p=translater';
$msg = '';
$code = isset($_REQUEST['code']) ? $_REQUEST['code'] : '';
$tab = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : '';
$from = isset($_POST['from']) && $_POST['from'] != '-' ? $_POST['from'] : '';
$lang = isset($_POST['lang']) && $_POST['lang'] != '-' ? $_POST['lang'] : '';
$action = isset($_POST['action']) ? $_POST['action'] : '';
$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
$module = isset($_REQUEST['module']) ? $_REQUEST['module'] : '';

# Combos
$combo_backup_folder = array(
	'module' => __('Locales folders of each module'),
	'plugin' => __('Plugins folder root'),
	'public' => __('Public folder root'),
	'cache' => __('Cache folder of Dotclear'),
	'translater' =>__('Locales folder of translater')
);

# Tabs
$tabs = array(
	'about' => __('About'),
	'plugin' => __('Translate extensions'),
	'theme' => __('Translate themes'),
	'pack' =>  __('Import/Export'),
	'module' => __('Translate'),
	'setting' => __('Settings'),
	'summary' => __('Summary'),
	'lang' => __('Add/Remove'),
	'backup' => __('Backups')
);

# succes_codes
$succes = array(
	'save_setting' => __('Configuration successfully updated'),
	'update_lang' => __('Translation successfully updated'),
	'add_lang' => __('Translation successfully created'),
	'delete_lang' => __('Translation successfully deleted'),
	'create_backup' => __('Backups successfully create'),
	'restore_backup' => __('Backups successfully restored'),
	'delete_backup' => __('Backups successfully deleted'),
	'import_pack' => __('Package successfully imported'),
	'export_pack' => __('Package successfully exported')
);

# errors_codes
$errors = array(
	'save_setting' => __('Failed to update settings: %s'),
	'update_lang' => __('Failed to update translation: %s'),
	'add_lang' => __('Failed to create translation: %s'),
	'delete_lang' => __('Failed to delete translation: %s'),
	'create_backup' => __('Failed to create backups: %s'),
	'restore_backup' => __('Failed to restore backups: %s'),
	'delete_backup' => __('Failed to delete backups: %s'),
	'import_pack' => __('Failed to import package: %s'),
	'export_pack' => __('Failed to export package: %s')
);

# Get infos on module wanted
try {
	$M = $O->getModule($module,$type);
}
catch(Exception $e) {
	$core->error->add(sprintf(
		__('Failed to launch translater: %s'),$e->getMessage()));
	$action = $module = $type = '';
	$M = false;
}
if (!empty($module) && !empty($type) && !$M) {
	$action = $module = $type = '';
	$M = false;
}

# Actions
if ('' != $action) {
	try {
		switch($action) {

		# Update settings
		case 'save_setting':
		if (empty($_POST['settings'])) break;

		if (empty($_POST['settings']['write_po'])
		 && empty($_POST['settings']['write_langphp'])) {
			throw new Exception('You must choose one file format at least');
		}
		$settings = array();
		foreach($O->getSettings() as $k => $v) {
			$settings[$k] = isset($_POST['settings'][$k]) ? $_POST['settings'][$k] : '';
		}
		$O->setSettings($settings);
		$tab = 'setting';
		break;

		# Save changes on translation
		case 'update_lang':
		if (!empty($_POST['wildcard_id']) 
		 && !empty($_POST['wildcard_str'])) {
			$_POST['fields'] = array_merge(
				$_POST['fields'],
				array($_POST['wildcard_id'] => $_POST['wildcard_str']));
			$_POST['groups'] = array_merge(
				$_POST['groups'],
				array($_POST['wildcard_id'] => $_POST['wildcard_group']));
		}
		$O->updLang($module,$lang,$_POST['groups'],$_POST['fields']);
		$tab = $lang;
		break;

		# create translation
		case 'add_lang':
		if (empty($lang)) break;

		$O->addLang($module,$lang,$from);
		$tab = $lang;
		break;

		# Delete translation
		case 'delete_lang':
		if (empty($lang)) break;

		$O->delLang($module,$lang);
		$tab = 'lang';
		break;

		# Create backups
		case 'create_backup':
		if (empty($_POST['modules'])
		 || empty($_POST['langs'])) break;

		foreach($_POST['modules'] as $b_module) {
			$b_list = $O->listLangs($b_module);
			foreach($_POST['langs'] as $b_lang) {
				if (isset($b_list[$b_lang]))
					$O->createBackup($b_module,$b_lang);
			}
		}
		$tab = 'backup';
		break;

		# Restore translations from backup
		case 'restore_backup':
		if (empty($_POST['modules']) || empty($_POST['files'])) break;

		sort($_POST['files']);
		$done = false;
		foreach($_POST['modules'] as $b_module) {
			$b_list = $O->listBackups($b_module,true);
			foreach($_POST['files'] as $b_file) {
				if (in_array($b_file,$b_list))
					$O->restoreBackup($b_module,$b_file);
					$done = true;
			}
		}
		if (!$done)
			throw new Exception(__('Nothing to restore')); //?
		$tab = 'backup';
		break;

		# Deletes translations backups
		case 'delete_backup':
		if (empty($_POST['modules']) 
		 || empty($_POST['files'])) break;

		$done = false;
		foreach($_POST['modules'] as $b_module) {
			$b_list = $O->listBackups($b_module,true);
			foreach($_POST['files'] as $b_file) {
				if (in_array($b_file,$b_list))
					$O->deleteBackup($b_module,$b_file);
					$done = true;
			}
		}
		if (!$done)
			throw new Exception(__('Nothing to backup'));
		$tab = 'backup';
		break;

		# Import langs package
		case 'import_pack':
		$O->importPack($_POST['modules'],$_FILES['packfile']);
		$tab = 'pack';
		break;

		# Export langs package
		case 'export_pack':
		if (empty($_POST['modules']) 
		 || empty($_POST['entries'])) break;

		$O->exportPack($_POST['modules'],$_POST['entries']);
		$tab = 'pack';
		break;

		# Unkonw action
		default:
		$tab = 'setting';
		throw new Exception('Unknow action '.$action);
		break;
		}
	}
	catch(Exception $e) {
		$core->error->add(sprintf($errors[$action],$e->getMessage()));
	}
	if (!$core->error->flag())
		http::redirect($p_url.'&module='.$module.'&type='.$type.'&tab='.$tab.'&code='.$action);
}

# get action message
if (isset($succes[$code]))
	$msg = $succes[$code];

# Header
echo
'<html>'.
'<head>'.
'<title>'.__('Translater').'</title>'.
dcPage::jsLoad('js/_posts_list.js').
dcPage::jsPageTabs($tab).
'<style type="text/css">'.
'<!--'.
' #help { background: #FFFFFF; }'.
' -->'.
'</style>'.
'</head>'.
'<body>';

# Simple page
if ($O->light_face)
	require dirname(__FILE__).'/inc/simple.php';

# Module page
elseif (!$M)
	require dirname(__FILE__).'/inc/modules.php';

# Modules page 
else
	require dirname(__FILE__).'/inc/module.php';

# Footer
echo 
'<hr class="clear"/>
<div class="two-cols">
<form method="post" action="'.$p_url.'" id="advancedmode">
<p class="col left">';
if ($O->light_face) {
	echo 
	'<input class="submit" type="submit" name="save" value="'.__('Switch to advance mode').'" />'.
	form::hidden(array('settings[light_face]'),'0');
} else {
	echo 
	'<input class="submit" type="submit" name="save" value="'.__('Switch to light mode').'" />'.
	form::hidden(array('settings[light_face]'),'1');
}
echo 
$core->formNonce().
form::hidden(array('settings[plugin_menu]'),$O->plugin_menu).
form::hidden(array('settings[theme_menu]'),$O->theme_menu).
form::hidden(array('settings[backup_auto]'),$O->backup_auto).
form::hidden(array('settings[backup_limit]'),$O->backup_limit).
form::hidden(array('settings[backup_folder]'),$O->backup_folder).
form::hidden(array('settings[write_po]'),$O->write_po).
form::hidden(array('settings[write_langphp]'),$O->write_langphp).
form::hidden(array('settings[parse_nodc]'),$O->parse_nodc).
form::hidden(array('settings[parse_comment]'),$O->parse_comment).
form::hidden(array('settings[import_overwrite]'),$O->import_overwrite).
form::hidden(array('settings[export_filename]'),$O->export_filename).
form::hidden(array('tab'),'setting').
form::hidden(array('module'),$module).
form::hidden(array('type'),$type).
form::hidden(array('action'),'save_setting').
form::hidden(array('p'),'translater').'
</p>
<p class="col right">
translater - '.$core->plugins->moduleInfo('translater','version').'&nbsp;
<img alt="translater" src="'.DC_ADMIN_URL.'?pf=translater/icon.png" />
</p>
</form>
</div>
</body></html>';
?>