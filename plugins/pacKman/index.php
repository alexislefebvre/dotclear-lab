<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of pacKman, a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) return;

# Check user perms
dcPage::checkSuper();

# Init some vars
$p_url 	= 'plugin.php?p=pacKman';
$tab = isset($_REQUEST['t']) ? $_REQUEST['t'] : 'settings';
$action = isset($_POST['action']) ? $_POST['action'] : '';
$type = isset($_POST['type']) && in_array($_POST['type'],
	array('plugins','themes','repository')) ? $_POST['type'] : '';

# Load class
$O = new dcPackman($core);
$S =& $core->blog->settings;

# Set up first time settings
if (!$S->packman_pack_repository || !$S->packman_pack_filename) {
	$S->setNamespace('packman');
	$S->put('packman_menu_plugins',false,
		'boolean','Add link to pacKman in plugins page',true,false);
	$S->put('packman_pack_overwrite',false,
		'boolean','Overwrite existing package',true,false);
	$S->put('packman_pack_filename','%type%-%id%-%version%',
		'string','Name of package',true,false);
	$S->put('packman_pack_repository',path::real($core->blog->public_path),
		'string','Path to package repository',true,false);
	$S->put('packman_pack_excludefiles','*.zip,*.tar,*.tar.gz',
		'string','Extra files to exclude from package',true,false);
	http::redirect($p_url.'&t='.$tab);
}

# List plugins and themes
$themes = new dcModules($core);
$themes->loadModules($core->blog->themes_path,null);
$plugins = $core->plugins;

# Paths
$plugins_path = path::real(array_pop(explode(PATH_SEPARATOR, DC_PLUGINS_ROOT)));
$themes_path = $core->blog->themes_path;
$repo_path = $S->packman_pack_repository;

# Actions
try {

	# Settings
	if ($action == 'save_settings') {
		if (!is_writable($_POST['settings']['repository']))
			throw new Exception('Path to repository is not writable');

		if (empty($_POST['settings']['filename']))
			throw new Exception('You must specify the name of package to export');

		$S->setNamespace('packman');
		$S->put('packman_menu_plugins',$_POST['settings']['plugins'],
			'boolean','Add link to pacKman in plugins page',true,false);
		$S->put('packman_pack_overwrite',$_POST['settings']['overwrite'],
			'boolean','Overwrite existing package',true,false);
		$S->put('packman_pack_filename',$_POST['settings']['filename'],
			'string','Name of package',true,false);
		$S->put('packman_pack_repository',path::real($_POST['settings']['repository']),
			'string','Path to package repository',true,false);
		$S->put('packman_pack_excludefiles',$_POST['settings']['excludefiles'],
			'string','Path to package repository',true,false);
		http::redirect($p_url.'&t=settings&setupdone=1');
	}

	# Pack
	if ($action == 'packup') {
		if ($type == '' || empty($_POST['modules']) || !is_array($_POST['modules']))
			throw new Exception('Nothing to pack');

		$modules = array_keys($_POST['modules']);

		foreach ($modules as $module) {

			if (!${$type}->moduleExists($module))
				throw new Exception('No such module '.$module);

			$exclude = explode(',',$S->packman_pack_excludefiles);

			$info = ${$type}->getModules($module);
			$info['id'] = $module;
			$info['type'] = $type == 'themes' ? 'theme' : 'plugin';

			$dest = dcPackman::path($S->packman_pack_repository,
				$S->packman_pack_filename,$info);

			if (file_exists($dest) && !$S->packman_pack_overwrite)
				throw new Exception('File allready exists');


			# --BEHAVIOR-- packmanBeforeCreatePackage
			$core->callBehavior('packmanBeforeCreatePackage',$info,$dest);


			dcPackman::pack($info,$dest,$exclude);


			# --BEHAVIOR-- packmanAfterCreatePackage
			$core->callBehavior('packmanAfterCreatePackage',$info,$dest);


		}

		if (!empty($_POST['redir'])) {
			$redir = $_POST['redir'];
			if (preg_match('!^plugins.php$!',$redir)) {
				$redir .=	'?'.http_build_query(array('tab' => 'packman-plugins', 'packupdone' => '1'),'','&');
			}
			http::redirect($redir);
		}
		http::redirect($p_url.'&t=packman-'.$type.'&packupdone=1');
	}

	# Delete
	if ($action == 'delete') {
		if ($type == '' || empty($_POST['modules']) || !is_array($_POST['modules']))
			throw new Exception('Nothing to delete');

		if ($type == 'plugins') 
			$proot = $plugins_path;
		elseif ($type == 'themes')
			$proot == $themes_path;
		else
			$proot == 'repository';

		foreach ($_POST['modules'] as $module => $root) {
			if (!file_exists($root) || !files::isDeletable($root))
				throw new Exception('Undeletable file: '.$root);

			unlink($root);
		}
		http::redirect($p_url.'&t=repository&deletedone='.$type);
	}

	# Install 
	if ($action == 'install') {
		if ($type == '' || empty($_POST['modules']) || !is_array($_POST['modules']))
			throw new Exception('Nothing to install');

		foreach ($_POST['modules'] as $id => $root) {


			# --BEHAVIOR-- packmanBeforeInstallPackage
			$core->callBehavior('packmanBeforeInstallPackage',$type,$id,$root);


			if ($type == 'plugins')
				$ret_code = $plugins->installPackage($root,$plugins);

			if ($type == 'themes')
				$ret_code = $themes->installPackage($root,$themes);


			# --BEHAVIOR-- packmanAfterInstallPackage
			$core->callBehavior('packmanAfterInstallPackage',$type,$id,$root);


		}
		http::redirect($p_url.'&t=repository&installdone='.$type);
	}

	# Copy
	if ($action == 'copy_to_plugins' || $action == 'copy_to_themes' || $action == 'copy_to_repository') {
		if ($type == '' || empty($_POST['modules']) || !is_array($_POST['modules']))
			throw new Exception('Nothing to copy');

		if ($action == 'copy_to_plugins') $dest = $plugins_path;
		if ($action == 'copy_to_themes') $dest = $themes_path;
		if ($action == 'copy_to_repository') $dest = $repo_path;

		foreach ($_POST['modules'] as $id => $root) {
			file_put_contents($dest.'/'.basename($root),file_get_contents($root));
		}
		http::redirect($p_url.'&t=repository&copydone='.$type);
	}

	# Move
	if ($action == 'move_to_plugins' || $action == 'move_to_themes' || $action == 'move_to_repository') {
		if ($type == '' || empty($_POST['modules']) || !is_array($_POST['modules']))
			throw new Exception('Nothing to move');

		if ($action == 'move_to_plugins') $dest = $plugins_path;
		if ($action == 'move_to_themes') $dest = $themes_path;
		if ($action == 'move_to_repository') $dest = $repo_path;

		foreach ($_POST['modules'] as $id => $root) {
			file_put_contents($dest.'/'.basename($root),file_get_contents($root));
			unlink($root);
		}
		http::redirect($p_url.'&t=repository&movedone='.$type);
	}
}
catch(Exception $e) {
	$core->error->add($e->getMessage());
}

# Display
echo '
<html><head><title>'.__('pacKman').'</title>
'.dcPage::jsLoad('js/_posts_list.js').dcPage::jsPageTabs($tab);


# --BEHAVIOR-- packmanAdminHeader
$core->callBehavior('packmanAdminHeader',$core,$tab);


echo '
</head><body>
<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; 
<a href="'.$p_url.'">pac<img alt="'.__('pacKman').'" src="index.php?pf=pacKman/icon.png" />man</a></h2>';

libPackman::tab($plugins->getModules(),'plugins');
libPackman::tab($themes->getModules(),'themes');


# --BEHAVIOR-- packmanAdminTabs
$core->callBehavior('packmanAdminTabs',$core,$tab);


if (is_writable(DC_TPL_CACHE)) {
	echo '<div class="multi-part" id="repository" title="'. __('Repository').'">';
	libPackman::repo(dcPackman::getPackages($core,$plugins_path),'plugins');
	libPackman::repo(dcPackman::getPackages($core,$themes_path),'themes');
	libPackman::repo(dcPackman::getPackages($core,$repo_path),'repository');
	echo '</div>';
}

echo '<div class="multi-part" id="settings" title="'. __('Settings').'">';

if (!is_writable(DC_TPL_CACHE))
	echo '<p class="error">'.__('Cache directory is not writable, packman repository functions are unavailable').'</p>';

if (isset($_REQUEST['setupdone']))
	echo '<p class="message">'.__('Configuration successfully saved').'</p>';

echo '
<form method="post" action="'.$p_url.'">
<p><label class="classic">'.
form::checkbox(array('settings[plugins]'),'1',$S->packman_menu_plugins).' '.
__('Enable menu on extensions page').'</label></p>
<p><label class="classic">'.
form::checkbox(array('settings[overwrite]'),'1',$S->packman_pack_overwrite).' '.
__('Overwrite existing package').'</label></p>
<p><label class="classic">'.__('Name of exported package').'<br />'.
form::field(array('settings[filename]'),65,255,$S->packman_pack_filename).'</label></p>
<p class="form-note">'.__('You can use wildcard related to extension like:').' 
%type%, %id%, %version%, %author%, %time%
<br />'.__('Default:').' %type%-%id%-%version%</p>
<p><label class="classic">'.__('Path to repository').'<br />'.
form::field(array('settings[repository]'),65,255,$S->packman_pack_repository).'</label></p>
<p class="form-note">'.__('This path must be in Dotclear and must be writable').'
<br />'.__('Default:').' '.path::real($core->blog->public_path).'</p>
<p><label class="classic">'.__('Extra files to exclude from package').'<br />'.
form::field(array('settings[excludefiles]'),65,255,$S->packman_pack_excludefiles).'</label></p>
<p class="form-note">'.__('Comma separated list with * for wildcards, in addition to:').
implode(', ',dcPackman::$exclude).'
<br />'.__('Default:').' *.zip,*.tar,*.tar.gz</p>
<p class="clear">
<input type="submit" name="save" value="'.__('save').'" />'.
$core->formNonce().
form::hidden(array('t'),'settings').
form::hidden(array('action'),'save_settings').
form::hidden(array('p'),'pacKman').'
</p>
</form>
</div>
<hr class="clear"/>
<p class="right">
pacKman - '.$core->plugins->moduleInfo('pacKman','version').'&nbsp;
<img alt="'.__('pacKman').'" src="index.php?pf=pacKman/icon.png" />
</p></body></html>';
?>