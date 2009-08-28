<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcAdvancedCleaner, a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

if (!$core->auth->isSuperAdmin()){return;}

if ($core->blog->settings->dcadvancedcleaner_behavior_active === null) {

	$core->blog->settings->setNameSpace('dcAdvancedCleaner');
	$core->blog->settings->put('dcadvancedcleaner_behavior_active',
		true,'boolean','',false,true);
	$core->blog->settings->put('dcadvancedcleaner_dcproperty_hide',
		true,'boolean','',false,true);
	$core->blog->settings->setNameSpace('system');

	http::redirect($p_url.'&t=dcb');
}

# Localized l10n
__('Settings'); __('settings'); __('setting');
__('Tables'); __('tables'); __('table');
__('Plugins'); __('plugins'); __('plugin');
__('Themes'); __('themes'); __('theme');
__('Caches'); __('caches'); __('cache');
__('Versions'); __('versions'); __('version');
__('delete table');
__('delete cache files');
__('delete plugin files');
__('delete theme files');
__('delete the version number');
__('Uninstall extensions');

# vars
$p_url = 'plugin.php?p=dcAdvancedCleaner';
$msg = isset($_GET['msg']) ? true : false;
$for = isset($_GET['for']) ? $_GET['for'] : '';
$tab = isset($_REQUEST['t']) ? $_REQUEST['t'] : 'records';
$entries = isset($_POST['entries']) ? $_POST['entries'] : '';
$action = isset($_POST['action']) ? $_POST['action'] : '';
$type = isset($_POST['type']) ? $_POST['type'] : '';

# Combos
$combo_settings = array(
	__('delete global settings') => 'delete_global',
	__('delete blog settings') => 'delete_local',
	__('delete all settings') =>'delete_all'
);
$combo_bi = array(
	__('delete') => 'delete',
	__('empty') => 'empty'
);
$combo_delete = array(
	__('delete') => 'delete'
);
$combo_type = array(
	'settings' => array('delete_global','delete_local','delete_all'),
	'tables' => array('empty','delete'),
	'plugins' => array('empty','delete'),
	'themes' => array('empty','delete'),
	'caches' => array('empty','delete'),
	'versions' => array('delete')
);
$combo_help = array(
	'settings' => __('Namespaces registered in dcSettings'),
	'tables' => __('All database tables of Dotclear'),
	'plugins' => __('Folders from plugins directories'),
	'themes' => __('Folders from blog themes directory'),
	'caches' => __('Folders from cache directory'),
	'versions' => __('Versions registered in table "version" of Dotclear')
);

# This plugin settings
if ($action == 'dcadvancedcleaner_settings') {

	$core->blog->settings->setNameSpace('dcAdvancedCleaner');
	$core->blog->settings->put('dcadvancedcleaner_behavior_active',
		isset($_POST['dcadvancedcleaner_behavior_active']),'boolean');
	$core->blog->settings->put('dcadvancedcleaner_dcproperty_hide',
		isset($_POST['dcadvancedcleaner_dcproperty_hide']),'boolean');
	$core->blog->settings->setNameSpace('system');

	http::redirect($p_url.'&t=dcb&msg=1');
}

# Actions
if (!empty($entries) 
 && isset($combo_type[$type]) 
 && in_array($action,$combo_type[$type])) {

	try {
		foreach($entries as $v) {
			dcAdvancedCleaner::execute($core,$type,$action,$v);
		}

		# Redirection on success
		http::redirect($p_url.'&t='.$tab.'&msg=done');
	}
	catch(Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# Lists
$settings = dcAdvancedCleaner::getSettings($core);
$tables = dcAdvancedCleaner::getTables($core);
$plugins = dcAdvancedCleaner::getPlugins($core);
$themes = dcAdvancedCleaner::getThemes($core);
$caches = dcAdvancedCleaner::getCaches($core);
$versions = dcAdvancedCleaner::getVersions($core);

?>
<html>
 <head>
  <title><?php echo __('Advanced cleaner'); ?></title>
  <style type="text/css">
  .listDcAdvancedCleaner {
	float:left;
	padding:10px;
	margin:10px 10px 0 0;
	border:1px solid #CCCCCC;
	background-color: #FCFCFC;
  }
  .bombDcAdvancedCleaner {
	padding:14px 0 2px 36px;
	background:url(index.php?pf=dcAdvancedCleaner/icon-b.png) no-repeat;
  }
  </style>
  <?php echo dcPage::jsToolBar().dcPage::jsPageTabs($tab); ?>

<?php 

# --BEHAVIOR-- dcAdvancedCleanerAdminHeader
$core->callBehavior('dcAdvancedCleanerAdminHeader',$core,$p_url,$tab);

?>
 </head>
 <body>
  <h2 class="bombDcAdvancedCleaner"><?php echo html::escapeHTML($core->blog->name).' &rsaquo; '.__('Advanced cleaner'); ?></h2>
  <p class="static-msg"><?php echo __('Beware: All actions done here are irreversible and are directly applied'); ?></p>
  <?php if (!empty($msg)) { echo '<p class="message">'.__('Action successfully done').'</p>'; } ?>

  <div class="multi-part" id="records" title="<?php echo __('Records'); ?>">
<?php
drawDcAdvancedCleanerLists('settings',$settings,$combo_settings,$combo_help['settings'],'records');
drawDcAdvancedCleanerLists('tables',$tables,$combo_bi,$combo_help['tables'],'records');
drawDcAdvancedCleanerLists('versions',$versions,$combo_delete,$combo_help['versions'],'records');
?>
  </div>

  <div class="multi-part" id="folders" title="<?php echo __('Folders'); ?>">
<?php
drawDcAdvancedCleanerLists('plugins',$plugins,$combo_bi,$combo_help['plugins'],'foldlers');
drawDcAdvancedCleanerLists('themes',$themes,$combo_bi,$combo_help['themes'],'folders');
drawDcAdvancedCleanerLists('caches',$caches,$combo_bi,$combo_help['caches'],'folders');
?>
  </div>

<?php 

# --BEHAVIOR-- dcAdvancedCleanerAdminTabs
$core->callBehavior('dcAdvancedCleanerAdminTabs',$core,$p_url);

?>

  <div class="multi-part" id="dcb" title="<?php echo __('Settings'); ?>">
   <form method="post" action="plugin.php">
    <p>
	 <label class="classic"><?php echo
	  form::checkbox(array('dcadvancedcleaner_behavior_active'),'1',
	   $core->blog->settings->dcadvancedcleaner_behavior_active).' '.
      __('Activate behaviors'); ?>
	 </label>
	</p>
    <p>
	 <label class="classic"><?php echo
	  form::checkbox(array('dcadvancedcleaner_dcproperty_hide'),'1',
	   $core->blog->settings->dcadvancedcleaner_dcproperty_hide).' '.
      __('Hide Dotclear default properties in actions tabs'); ?>
	 </label>
	</p>
	<p>
     <input type="submit" name="submit" value="<?php echo __('Save'); ?>" />
      <?php echo 
       form::hidden(array('p'),'dcAdvancedCleaner').
       form::hidden(array('t'),'dcb').
	   form::hidden(array('action'),'dcadvancedcleaner_settings').
       $core->formNonce();
      ?>
	 </p>
	</form>
  </div>
  <p class="clear">&nbsp;</p>
  <hr class="clear"/>
  <p class="right">
   dcAdvancedCleaner - <?php echo $core->plugins->moduleInfo('dcAdvancedCleaner','version'); ?>&nbsp;
   <img alt="dcMiniUrl" src="index.php?pf=dcAdvancedCleaner/icon.png" />
  </p>
 </body>
</html>
<?php

function drawDcAdvancedCleanerLists($type,$rs,$actions,$help='',$tab='records')
{
	echo 
	'<div class="listDcAdvancedCleaner">'.
	'<h2>'.__(ucfirst($type)).'</h2>'.
	'<p class="form-note">'.$help.'</p>';
	
	if (empty($rs)) {
		echo 
		'<p>'.sprintf(__('There is no %s'),__(substr($type,0,-1))).'</p>';
	} else {

		echo
		'<p>'.sprintf(__('There are %s %s'),count($rs),__($type)).'</p>'.
		'<form method="post" action="plugin.php">'.
		'<table><thead><tr>'.
		'<th>'.__('Name').'</th><th>'.__('Objects').'</th>'.
		'</tr></thead><tbody>';

		foreach($rs as $k => $v) {

			$offline = in_array($v['key'],dcAdvancedCleaner::$dotclear[$type]);

			if ($GLOBALS['core']->blog->settings->dcadvancedcleaner_dcproperty_hide && $offline)
				continue;

			echo 
			'<tr class="line'.
			($offline ? ' offline' : '').
			'">'.
			'<td class="nowrap"><label class="classic">'.
			form::checkbox(array('entries['.$k.']'),html::escapeHTML($v['key'])).' '.$v['key'].'</label></td>'.
			'<td class="nowrap">'.$v['value'].'</td>'.
			'</tr>';
		}

		echo
		'</tbody></table>'.
		'<p>'.__('Action on selected rows:').'<br />'.
		form::combo(array('action'),$actions).
		'<input type="submit" value="'.__('ok').'" />'.
		form::hidden(array('p'),'dcAdvancedCleaner').
		form::hidden(array('t'),$tab).
		form::hidden(array('type'),$type).
		$GLOBALS['core']->formNonce().'</p>'.
		'</form>';
	}
	echo 
	'</div>';
}
?>