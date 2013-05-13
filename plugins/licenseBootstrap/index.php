<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of licenseBootstrap, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2013 Jean-Christian Denis and contributors
# contact@jcdenis.fr
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}
dcPage::checkSuper();

$default_tab = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : 'setting';
$section = isset($_REQUEST['section']) ? $_REQUEST['section'] : '';

# Default lists
$default_exts = licenseBootstrap::getDefaultExtensions();
$default_headers = licenseBootstrap::getDefaultLicenses();

# Settings
$core->blog->settings->addNamespace('licenseBootstrap');
$addfull = (boolean) $core->blog->settings->licenseBootstrap->licensebootstrap_addfull;
$overwrite = (boolean) $core->blog->settings->licenseBootstrap->licensebootstrap_overwrite;
$license = $core->blog->settings->licenseBootstrap->licensebootstrap_license;
if (empty($license)) $license = 'gpl2';
$files_exts = licenseBootstrap::decode($core->blog->settings->licenseBootstrap->licensebootstrap_files_exts);
if (!is_array($files_exts)) $files_exts = $default_exts;
$licenses_headers = licenseBootstrap::decode($core->blog->settings->licenseBootstrap->licensebootstrap_licenses_headers);
if (!is_array($licenses_headers)) $licenses_headers = array();
$exclusion = $core->blog->settings->licenseBootstrap->licensebootstrap_exclusion;
$packman_behavior = $core->blog->settings->licenseBootstrap->licensebootstrap_packman_behavior;
$translater_behavior = $core->blog->settings->licenseBootstrap->licensebootstrap_translater_behavior;

try
{
	# Reset settings
	if (isset($_POST['reset_settings']))
	{
		$core->blog->settings->licenseBootstrap->put('licensebootstrap_addfull',true);
		$core->blog->settings->licenseBootstrap->put('licensebootstrap_overwrite',false);
		$core->blog->settings->licenseBootstrap->put('licensebootstrap_license','gpl2');
		$core->blog->settings->licenseBootstrap->put('licensebootstrap_files_exts',licenseBootstrap::encode($default_exts));
		$core->blog->settings->licenseBootstrap->put('licensebootstrap_licenses_headers',licenseBootstrap::encode($default_headers));
		$core->blog->settings->licenseBootstrap->put('licensebootstrap_exclusion','');
		$core->blog->settings->licenseBootstrap->put('licensebootstrap_packman_behavior',false);
		$core->blog->settings->licenseBootstrap->put('licensebootstrap_translater_behavior',false);
		
		http::redirect($p_url.'&tab=setting&section='.$section.'&done=1');
	}
	
	# Save settings
	if (isset($_POST['save_settings']))
	{
		$addfull = !empty($_POST['addfull']);
		$overwrite = !empty($_POST['overwrite']);
		$license = $_POST['license'];
		$files_exts = is_array($_POST['files_exts']) ? $_POST['files_exts'] : array();
		$licenses_headers = is_array($_POST['licenses_headers']) ? $_POST['licenses_headers'] : array();
		$exclusion = $_POST['exclusion'];
		$packman_behavior = !empty($_POST['packman_behavior']);
		$translater_behavior = !empty($_POST['translater_behavior']);
		
		$core->blog->settings->licenseBootstrap->put('licensebootstrap_addfull',$addfull);
		$core->blog->settings->licenseBootstrap->put('licensebootstrap_overwrite',$overwrite);
		$core->blog->settings->licenseBootstrap->put('licensebootstrap_license',$license);
		$core->blog->settings->licenseBootstrap->put('licensebootstrap_files_exts',licenseBootstrap::encode($files_exts));
		$core->blog->settings->licenseBootstrap->put('licensebootstrap_licenses_headers',licenseBootstrap::encode($licenses_headers));
		$core->blog->settings->licenseBootstrap->put('licensebootstrap_exclusion',$exclusion);
		$core->blog->settings->licenseBootstrap->put('licensebootstrap_packman_behavior',$packman_behavior);
		$core->blog->settings->licenseBootstrap->put('licensebootstrap_translater_behavior',$translater_behavior);
		
		http::redirect($p_url.'&tab=setting&section='.$section.'&done=1');
	}
	# Object
	$LB = new licenseBootstrap($core,$files_exts,$license,$licenses_headers[$license],$addfull,$overwrite,$exclusion);
	
	# Add license to files
	if (isset($_POST['add_license']))
	{
		if (!isset($_POST['type']) || empty($_POST['modules']) || !is_array($_POST['modules']))
		{
			throw new Exception('Nothing to pack');
		}
		
		$type = $_POST['type'] == 'theme' ? 'theme' : 'plugin';
		$modules = array_keys($_POST['modules']);
		
		foreach ($modules as $id)
		{
			$LB->writeModuleLicense($type,$id);
		}
		
		if (!empty($_POST['redir']))
		{
			http::redirect($_POST['redir']);
		}
		http::redirect($p_url.'&tab='.$type.'&done=1');
	}
}
catch(Exception $e)
{
	$core->error->add($e->getMessage());
}

$title = '';
if ($default_tab == 'plugin') {
	$title = __('Plugins');
}
elseif ($default_tab == 'theme') {
	$title = __('Theme');
}
elseif ($default_tab == 'setting') {
	$title = __('Settings');
}
$title = html::escapeHTML($title);

# Display
echo 
'<html><head><title>'.__('License bootstrap');
if (!empty($title)) {
	echo ' - '.$title;
}
echo 
'</title></head><body>'.
'<h2>'.__('License bootstrap');
if (!empty($title)) {
	echo ' &rsaquo; <span class="page-title">'.$title.'</span>';
}
echo 
' - <a class="button" href="'.$p_url.'&amp;tab=plugin">'.__('Plugins').'</a>'.
' - <a class="button" href="'.$p_url.'&amp;tab=theme">'.__('Themes').'</a>'.
'</h2>';

if ($default_tab == 'plugin')
{
	libLicenseBootstrap::tab($core->plugins->getModules(),'plugin');
}
elseif ($default_tab == 'theme')
{
	$themes = new dcModules($core);
	$themes->loadModules($core->blog->themes_path,null);
	libLicenseBootstrap::tab($themes->getModules(),'theme');
}
elseif ($default_tab == 'setting')
{
	if (isset($_REQUEST['done']))
	{
		dcPage::message(__('Configuration successfully saved'));
	}
	echo '<form id="setting-form" method="post" action="'.$p_url.'">';
	
	if ($core->plugins->moduleExists('pacKman') || $core->plugins->moduleExists('translater'))
	{
		echo '<fieldset id="settingbehavior"><legend>'.__('Behaviors').'</legend>';
		
		if ($core->plugins->moduleExists('pacKman'))
		{
			echo '
			<p><label class="classic">'.
			form::checkbox(array('packman_behavior'),'1',$packman_behavior).' '.
			__('Add license before create package with plugin pacKman').'</label></p>';
		}
		if ($core->plugins->moduleExists('translater'))
		{
			echo '
			<p><label class="classic">'.
			form::checkbox(array('translater_behavior'),'1',$translater_behavior).' '.
			__('Add license after create lang file with plugin translater').'</label></p>';
		}
		echo '</fieldset>';
	}
	
	echo '
	<fieldset id="settingfile"><legend>'.__('Files').'</legend>
	<p><label class="classic">'.
	form::checkbox(array('overwrite'),'1',$overwrite).' '.
	__('Overwrite existing license').'</label></p>
	<p><label class="classic">'.
	form::checkbox(array('addfull'),'1',$addfull).' '.
	__('Add full LICENSE file to module root').'</label></p>';
	
	foreach($default_exts as $ext)
	{
		echo '
		<p><label class="classic">'.
		form::checkbox(array('files_exts[]'),$ext,in_array($ext,$files_exts)).' '.
		sprintf(__('Add license to the header of %s files'),$ext).'</label></p>';
	}
	
	echo '
	<p><label>'.__('Exclusion:').
	form::field('exclusion',60,255,html::escapeHTML($exclusion)).'
	</label></p>
	</fieldset>
	
	<fieldset id="settingheader"><legend>'.__('Headers').'</legend>';
	
	foreach($default_headers as $type => $header)
	{
		$text = isset($licenses_headers[$type]) ? $licenses_headers[$type] : $header;
		
		echo '
		<p><label class="classic">'.
		form::radio(array('license'),$type,($license == $type)).' '.
		sprintf(__('License %s:'),$type).'</label></p>
		<p class="area">'.
		form::textarea('licenses_headers['.$type.']',50,10,html::escapeHTML($text)).'
		</p>';
	}
	echo '</fieldset>';
	
	echo '
	<p class="clear">
	<input type="submit" name="save_settings" value="'.__('save').'" /> 
	<input type="submit" name="reset_settings" value="'.__('Reset settings').'" />'.
	$core->formNonce().
	form::hidden(array('section'),$section).
	form::hidden(array('tab'),'settings').
	form::hidden(array('p'),'licenseBootstrap').'
	</p>
	</form>
	</div>';
}
dcPage::helpBlock('licenseBootstrap');

echo '<hr class="clear"/><p class="right">
<a class="button" href="'.$p_url.'&amp;tab=setting">'.__('Settings').'</a> - 
licenseBootstrap - '.$core->plugins->moduleInfo('licenseBootstrap','version').'&nbsp;
<img alt="'.__('licenseBootstrap').'" src="index.php?pf=licenseBootstrap/icon.png" />
</p></body></html>';
?>