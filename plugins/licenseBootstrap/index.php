<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of licenseBootstrap, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}
dcPage::checkSuper();

$default_tab = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : 'setting';

# Default lists
$default_exts = licenseBootstrap::getDefaultExts();
$default_headers = licenseBootstrap::getDefaultHeaders();

# Modules lists
$themes = new dcModules($core);
$themes->loadModules($core->blog->themes_path,null);
$plugins = $core->plugins;

# Settings
$s =& $core->blog->settings;
$overwrite = (boolean) $s->licensebootstrap_overwrite;
$license = $s->licensebootstrap_license;
if (empty($license)) $license = 'gpl2';
$files_exts = licenseBootstrap::decode($s->licensebootstrap_files_exts);
if (!is_array($files_exts)) $files_exts = $default_exts;
$licenses_headers = licenseBootstrap::decode($s->licensebootstrap_licenses_headers);
if (!is_array($licenses_headers)) $licenses_headers = array();

# Add to packman
$packman_behavior = $s->licensebootstrap_packman_behavior;

# Actions
try
{
	# Reset settings
	if (isset($_POST['reset_settings']))
	{
		$s->setNamespace('licenseBootstrap');
		$s->put('licensebootstrap_overwrite',false);
		$s->put('licensebootstrap_license','gpl2');
		$s->put('licensebootstrap_files_exts',
			licenseBootstrap::encode($default_exts));
		$s->put('licensebootstrap_licenses_headers',
			licenseBootstrap::encode($default_headers));
		$s->put('licensebootstrap_packman_behavior',false);
		$s->setNamespace('system');

		http::redirect($p_url.'&tab=setting&done=1');
	}

	# Save settings
	if (isset($_POST['save_settings']))
	{
		$overwrite = !empty($_POST['overwrite']);
		$license = $_POST['license'];
		$files_exts = is_array($_POST['files_exts']) ? 
			$_POST['files_exts'] : array();
		$licenses_headers = is_array($_POST['licenses_headers']) ? 
			$_POST['licenses_headers'] : array();
		$packman_behavior = !empty($_POST['packman_behavior']);

		$s->setNamespace('licenseBootstrap');
		$s->put('licensebootstrap_overwrite',$overwrite);
		$s->put('licensebootstrap_license',$license);
		$s->put('licensebootstrap_files_exts',
			licenseBootstrap::encode($files_exts));
		$s->put('licensebootstrap_licenses_headers',
			licenseBootstrap::encode($licenses_headers));
		$s->put('licensebootstrap_packman_behavior',$packman_behavior);
		$s->setNamespace('system');

		http::redirect($p_url.'&tab=setting&done=1');
	}

	# Add license to files
	if (isset($_POST['add_license']))
	{
		if (!isset($_POST['type']) 
		 || empty($_POST['modules']) || !is_array($_POST['modules']))
		{
			throw new Exception('Nothing to pack');
		}

		$type = $_POST['type'] == 'theme' ? 'theme' : 'plugin';
		$modules = array_keys($_POST['modules']);

		foreach ($modules as $id)
		{
			$info = ($type == 'theme') ? 
				$themes->getModules($id) :
				$plugins->getModules($id);

			if (null === $info)
			{
				throw new Exception('No such module '.$id);
			}

			licenseBootstrap::license($core,$type,$id,$info,$files_exts,
				$license,$licenses_headers[$license],$overwrite
			);
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

# Display
echo '
<html><head><title>'.__('License bootstrap').'</title>
'.dcPage::jsLoad('js/_posts_list.js').dcPage::jsPageTabs($default_tab).'
</head><body>
<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.
__('License bootstrap').'</h2>';

libLicenseBootstrap::tab($plugins->getModules(),'plugin');
libLicenseBootstrap::tab($themes->getModules(),'theme');

echo '<div class="multi-part" id="setting" title="'. __('Settings').'">';

if (isset($_REQUEST['done']) && $default_tab == 'setting')
	echo '<p class="message">'.__('Configuration successfully saved').'</p>';

echo '
<form method="post" action="'.$p_url.'">
<fieldset><legend>'.__('Files').'</legend>
<p><label class="classic">'.
form::checkbox(array('overwrite'),'1',$overwrite).' '.
__('Overwrite existing license').'</label></p>';

foreach($default_exts as $ext)
{
	echo '
	<p><label class="classic">'.
	form::checkbox(array('files_exts[]'),$ext,in_array($ext,$files_exts)).' '.
	sprintf(__('Add license to the header of %s files'),$ext).'</label></p>';
}

echo '
<p><label class="classic">'.
form::checkbox(array('packman_behavior'),'1',$packman_behavior).' '.
__('Add license before create package with plugin pacKman').'</label></p>
</fieldset>
<fieldset><legend>'.__('Headers').'</legend>';

foreach($default_headers as $type => $header)
{
	$text = isset($licenses_headers[$type]) ?
		$licenses_headers[$type] : 
		$header;

	echo '
	<p><label class="classic">'.
	form::radio(array('license'),$type,($license == $type)).' '.
	sprintf(__('License %s:'),$type).'</label></p>
	<p class="area">'.
	form::textarea('licenses_headers['.$type.']',50,10,html::escapeHTML($text)).'
	</p>';
}
echo '
</fieldset>
<p class="clear">
<input type="submit" name="save_settings" value="'.__('save').'" /> 
<input type="submit" name="reset_settings" value="'.__('Reset settings').'" />'.
$core->formNonce().
form::hidden(array('tab'),'settings').
form::hidden(array('p'),'licenseBootstrap').'
</p>
</form>
</div>
'.dcPage::helpBlock('licenseBootstrap').'

<hr class="clear"/>
<p class="right">
pacKman - '.$core->plugins->moduleInfo('licenseBootstrap','version').'&nbsp;
<img alt="'.__('licenseBootstrap').'" src="index.php?pf=licenseBootstrap/icon.png" />
</p></body></html>';
?>