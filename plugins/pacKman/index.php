<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of pacKman, a plugin for Dotclear 2.
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

# Init vars
$p_url = 'plugin.php?p=pacKman';
$default_tab = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : '';
$action = isset($_POST['action']) ? $_POST['action'] : '';
$type = isset($_POST['type']) && in_array($_POST['type'],
	array('plugins','themes','repository')) ? $_POST['type'] : '';

# Settings
$core->blog->settings->addNamespace('pacKman');
$packman_menu_plugins = $core->blog->settings->pacKman->packman_menu_plugins;
$packman_pack_nocomment = $core->blog->settings->pacKman->packman_pack_nocomment;
$packman_pack_overwrite = $core->blog->settings->pacKman->packman_pack_overwrite;
$packman_pack_filename = $core->blog->settings->pacKman->packman_pack_filename;
$packman_secondpack_filename = $core->blog->settings->pacKman->packman_secondpack_filename;
$packman_pack_repository = $core->blog->settings->pacKman->packman_pack_repository;
$packman_pack_excludefiles = $core->blog->settings->pacKman->packman_pack_excludefiles;

# List plugins and themes
$themes = new dcModules($core);
$themes->loadModules($core->blog->themes_path,null);
$plugins = $core->plugins;

# Paths
$ppexp = explode(PATH_SEPARATOR, DC_PLUGINS_ROOT);
$pppop = array_pop($ppexp);
$plugins_path = path::real($pppop);
$themes_path = $core->blog->themes_path;
$repo_path = $packman_pack_repository;

# Actions
try
{
	# Download
	if (isset($_REQUEST['package']) && empty($type))
	{
		$modules = array();
		if ($type == 'plugins')
		{
			$modules = dcPackman::getPackages($core,$plugins_path);
		}
		elseif ($type == 'themes')
		{
			$modules = dcPackman::getPackages($core,$themes_path);
		}
		else
		{
			$modules = array_merge(
				dcPackman::getPackages($core,dirname($repo_path.'/'.$packman_pack_filename)),
				dcPackman::getPackages($core,dirname($repo_path.'/'.$packman_secondpack_filename))
			);
		}
		if (empty($modules))
		{
			# Not found
			header('Content-Type: text/plain');
			http::head(404,'Not Found');
			exit;
		}
		foreach($modules as $f)
		{
			if (preg_match('/'.preg_quote($_REQUEST['package']).'$/',$f['root'])
			 && is_file($f['root']) && is_readable($f['root']))
			{
				
				# --BEHAVIOR-- packmanBeforeDownloadPackage
				$core->callBehavior('packmanBeforeDownloadPackage',$f,$type);
				
				header('Content-Type: application/zip');
				header('Content-Length: '.filesize($f['root']));
				header('Content-Disposition: attachment; filename="'.basename($f['root']).'"');
				readfile($f['root']);
				
				# --BEHAVIOR-- packmanAfterDownloadPackage
				$core->callBehavior('packmanAfterDownloadPackage',$f,$type);
				
				exit;
			}
		}
		# Not found
		header('Content-Type: text/plain');
		http::head(404,'Not Found');
		exit;
	}
	
	# Reset settings
	if (isset($_POST['reset_settings']))
	{
		$core->blog->settings->pacKman->put('packman_menu_plugins',false);
		$core->blog->settings->pacKman->put('packman_pack_nocomment',false);
		$core->blog->settings->pacKman->put('packman_pack_overwrite',false);
		$core->blog->settings->pacKman->put('packman_pack_filename','%type%-%id%-%version%');
		$core->blog->settings->pacKman->put('packman_secondpack_filename','%type%-%id%');
		$core->blog->settings->pacKman->put('packman_pack_repository',
			path::real(path::fullFromRoot($core->blog->settings->system->public_path,DC_ROOT))
		);
		$core->blog->settings->pacKman->put('packman_pack_excludefiles','*.zip,*.tar,*.tar.gz');

		http::redirect($p_url.'&tab=settings&setupdone=1');
	}
	
	# Save settings
	if (isset($_POST['save_settings']))
	{
		if (!is_writable($_POST['packman_pack_repository']))
		{
			throw new Exception(__('Path to repository is not writable'));
		}
		if (empty($_POST['packman_pack_filename']))
		{
			throw new Exception(__('You must specify the name of package to export'));
		}
		if (!is_writable(dirname($_POST['packman_pack_repository'].'/'.$_POST['packman_pack_filename'])))
		{
			throw new Exception(__('Path to first export package is not writable'));
		}
		if (!empty($_POST['packman_secondpack_filename']) 
		 && !is_writable(dirname($_POST['packman_pack_repository'].'/'.$_POST['packman_secondpack_filename'])))
		{
			throw new Exception(__('Path to second export package is not writable'));
		}
		
		$packman_menu_plugins = !empty($_POST['packman_menu_plugins']);
		$packman_pack_nocomment = !empty($_POST['packman_pack_nocomment']);
		$packman_pack_overwrite = !empty($_POST['packman_pack_overwrite']);
		$packman_pack_filename = $_POST['packman_pack_filename'];
		$packman_secondpack_filename = $_POST['packman_secondpack_filename'];
		$packman_pack_repository = path::real($_POST['packman_pack_repository']);
		$packman_pack_excludefiles = $_POST['packman_pack_excludefiles'];
		
		$core->blog->settings->pacKman->put('packman_menu_plugins',$packman_menu_plugins);
		$core->blog->settings->pacKman->put('packman_pack_nocomment',$packman_pack_nocomment);
		$core->blog->settings->pacKman->put('packman_pack_overwrite',$packman_pack_overwrite);
		$core->blog->settings->pacKman->put('packman_pack_filename',$packman_pack_filename);
		$core->blog->settings->pacKman->put('packman_secondpack_filename',$packman_secondpack_filename);
		$core->blog->settings->pacKman->put('packman_pack_repository',$packman_pack_repository);
		$core->blog->settings->pacKman->put('packman_pack_excludefiles',$packman_pack_excludefiles);
		
		http::redirect($p_url.'&tab=settings&setupdone=1');
	}
	
	# Pack
	if ($action == 'packup')
	{
		if ($type == '' || empty($_POST['modules']) || !is_array($_POST['modules']))
		{
			throw new Exception('Nothing to pack');
		}
		
		$modules = array_keys($_POST['modules']);
		
		foreach ($modules as $module)
		{
			if (!${$type}->moduleExists($module))
			{
				throw new Exception('No such module '.$module);
			}
			
			$info = ${$type}->getModules($module);
			$info['id'] = $module;
			$info['type'] = $type == 'themes' ? 'theme' : 'plugin';
			
			$root = $packman_pack_repository;
			$files = array($packman_pack_filename,$packman_secondpack_filename);
			$nocomment = $packman_pack_nocomment;
			$overwrite = $packman_pack_overwrite;
			$exclude = explode(',',$packman_pack_excludefiles);
			
			# --BEHAVIOR-- packmanBeforeCreatePackage
			$core->callBehavior('packmanBeforeCreatePackage',$info,$root,$files,$overwrite,$exclude,$nocomment);
			
			dcPackman::pack($info,$root,$files,$overwrite,$exclude,$nocomment);
			
			# --BEHAVIOR-- packmanAfterCreatePackage
			$core->callBehavior('packmanAfterCreatePackage',$info,$root,$files,$overwrite,$exclude,$nocomment);
			
		}
		
		if (!empty($_POST['redir']))
		{
			$redir = $_POST['redir'];
			
			if (preg_match('!^plugins.php$!',$redir))
			{
				$qa = array('tab' => 'packman-plugins', 'packupdone' => '1');
				$redir .=	'?'.http_build_query($qa,'','&');
			}
			http::redirect($redir);
		}
		http::redirect($p_url.'&tab=packman-'.$type.'&packupdone=1');
	}
	
	# Delete
	if ($action == 'delete')
	{
		if ($type == '' || empty($_POST['modules']) || !is_array($_POST['modules']))
		{
			throw new Exception('Nothing to delete');
		}
		if ($type == 'plugins') 
		{
			$proot = $plugins_path;
		}
		elseif ($type == 'themes')
		{
			$proot == $themes_path;
		}
		else
		{
			$proot == 'repository';
		}
		
		foreach ($_POST['modules'] as $module => $root)
		{
			if (!file_exists($root) || !files::isDeletable($root))
			{
				throw new Exception('Undeletable file: '.$root);
			}
			unlink($root);
		}
		http::redirect($p_url.'&tab=repository&deletedone='.$type);
	}
	
	# Install 
	if ($action == 'install')
	{
		if ($type == '' || empty($_POST['modules']) || !is_array($_POST['modules']))
		{
			throw new Exception('Nothing to install');
		}
		
		foreach ($_POST['modules'] as $id => $root)
		{
			
			# --BEHAVIOR-- packmanBeforeInstallPackage
			$core->callBehavior('packmanBeforeInstallPackage',$type,$id,$root);
			
			if ($type == 'plugins')
			{
				$ret_code = $plugins->installPackage($root,$plugins);
			}
			if ($type == 'themes')
			{
				$ret_code = $themes->installPackage($root,$themes);
			}
			
			# --BEHAVIOR-- packmanAfterInstallPackage
			$core->callBehavior('packmanAfterInstallPackage',$type,$id,$root);
			
		}
		http::redirect($p_url.'&tab=repository&installdone='.$type);
	}
	
	# Copy
	if ($action == 'copy_to_plugins'
	 || $action == 'copy_to_themes' 
	 || $action == 'copy_to_repository')
	{
		if ($type == '' || empty($_POST['modules']) || !is_array($_POST['modules']))
		{
			throw new Exception('Nothing to copy');
		}
		if ($action == 'copy_to_plugins')
		{
			$dest = $plugins_path;
		}
		elseif ($action == 'copy_to_themes')
		{
			$dest = $themes_path;
		}
		elseif ($action == 'copy_to_repository')
		{
			$dest = $repo_path;
		}
		
		foreach ($_POST['modules'] as $id => $root)
		{
			file_put_contents($dest.'/'.basename($root),file_get_contents($root));
		}
		http::redirect($p_url.'&tab=repository&copydone='.$type);
	}
	
	# Move
	if ($action == 'move_to_plugins'
	 || $action == 'move_to_themes' 
	 || $action == 'move_to_repository')
	{
		if ($type == '' || empty($_POST['modules']) || !is_array($_POST['modules']))
		{
			throw new Exception('Nothing to move');
		}
		if ($action == 'move_to_plugins')
		{
			$dest = $plugins_path;
		}
		elseif ($action == 'move_to_themes')
		{
			$dest = $themes_path;
		}
		elseif ($action == 'move_to_repository')
		{
			$dest = $repo_path;
		}
		
		foreach ($_POST['modules'] as $id => $root)
		{
			file_put_contents($dest.'/'.basename($root),file_get_contents($root));
			unlink($root);
		}
		http::redirect($p_url.'&tab=repository&movedone='.$type);
	}
}
catch(Exception $e)
{
	$core->error->add($e->getMessage());
}

$iswritable = libPackman::is_writable($packman_pack_repository,$packman_pack_filename);
if ($default_tab == '')
{
	$default_tab = $iswritable ? 'repository' : 'setting';
}
$title = '';
if ($default_tab == 'packman-plugins') {
	$title = sprintf(__('Pack up %s'),__('plugins'));
}
elseif ($default_tab == 'packman-themes') {
	$title = sprintf(__('Pack up %s'),__('themes'));
}
elseif ($default_tab == 'repository') {
	$title = __('Repositories of packages');
}
elseif ($default_tab == 'setting') {
	$title = __('Settings');
}
$title = html::escapeHTML($title);

# Display
echo '
<html><head><title>'.__('pacKman');
if (!empty($title)) {
	echo ' - '.$title;
}
echo 
'</title>
'.dcPage::jsLoad('js/_posts_list.js').
'<link rel="stylesheet" type="text/css" href="index.php?pf=pacKman/style.css" />';

# --BEHAVIOR-- packmanAdminHeader
$core->callBehavior('packmanAdminHeader',$core,$default_tab);

echo '
</head><body>
<h2>pac<img alt="'.__('pacKman').'" src="index.php?pf=pacKman/icon.png" />man';
if (!empty($title)) {
	echo ' &rsaquo; <span class="page-title">'.$title.'</span>';
}
echo 
' - <a class="button" href="'.$p_url.'&amp;tab=packman-plugins">'.__('Plugins').'</a>'.
' - <a class="button" href="'.$p_url.'&amp;tab=packman-themes">'.__('Themes').'</a>';
if ($iswritable) {
	echo ' - <a class="button" href="'.$p_url.'&amp;tab=repository">'.__('Repositories').'</a>';
}
echo 
'</h2>';

if ($default_tab == 'packman-plugins' && $iswritable)
{
	libPackman::tab($plugins->getModules(),'plugins',null,false);
}
elseif ($default_tab == 'packman-themes' && $iswritable)
{
	libPackman::tab($themes->getModules(),'themes',null,false);
}
elseif ($default_tab == 'repository' && $iswritable)
{
	$repo_path_modules = array_merge(
		dcPackman::getPackages($core,dirname($repo_path.'/'.$packman_pack_filename)),
		dcPackman::getPackages($core,dirname($repo_path.'/'.$packman_secondpack_filename))
	);
	$plugins_path_modules = dcPackman::getPackages($core,$plugins_path);
	$themes_path_modules = dcPackman::getPackages($core,$themes_path);
	
	echo '<div id="repository">';
	
	if (empty($plugins_path_modules) && empty($themes_path_modules) && empty($repo_path_modules))
	{
		echo '<p>'.__('There is no package').'</p>';
	}
	if (!empty($plugins_path_modules))
	{
		libPackman::repo($plugins_path_modules,'plugins');
	}
	if (!empty($themes_path_modules))
	{
		libPackman::repo($themes_path_modules,'themes');
	}
	if (!empty($repo_path_modules))
	{
		libPackman::repo($repo_path_modules,'repository');
	}
	
	echo '</div>';
}
else
{
	if (isset($_REQUEST['setupdone']))
	{
		dcPage::message(__('Configuration successfully saved'));
	}
	
	echo '<div id="settings">';
	
	if (!is_writable(DC_TPL_CACHE))
	{
		echo '<p class="error">'.__('Cache directory is not writable, packman repository functions are unavailable').'</p>';
	}
	echo '
	<form class="dtbfieldsettomenu" method="post" action="'.$p_url.'">
	<fieldset id="setting-behavior"><legend>'.__('Behaviors').'</legend>
	<p><label class="classic">'.
	form::checkbox(array('packman_menu_plugins'),'1',$packman_menu_plugins).' '.
	__('Enable menu on extensions page').'</label></p>
	<p><label class="classic">'.
	form::checkbox(array('packman_pack_nocomment'),'1',$packman_pack_nocomment).' '.
	__('Remove comments from files').'</label></p>
	<p><label class="classic">'.
	form::checkbox(array('packman_pack_overwrite'),'1',$packman_pack_overwrite).' '.
	__('Overwrite existing package').'</label></p>
	</fieldset>
	
	<fieldset id="setting-file"><legend>'.__('Packages').'</legend>
	<p><label class="classic">'.__('Name of exported package').'<br />'.
	form::field(array('packman_pack_filename'),65,255,$packman_pack_filename).'</label></p>
	<p><label class="classic">'.__('Name of second exported package').'<br />'.
	form::field(array('packman_secondpack_filename'),65,255,$packman_secondpack_filename).'</label></p>
	<p><label class="classic">'.__('Path to repository').'<br />'.
	form::field(array('packman_pack_repository'),65,255,$packman_pack_repository).'</label></p>';
	if ($core->blog->public_path)
	{
		echo'<p class="form-note">'.sprintf(__('Public directory is: %s'),$core->blog->public_path).'</p>';
	}
	echo '
	<p><label class="classic">'.__('Extra files to exclude from package').'<br />'.
	form::field(array('packman_pack_excludefiles'),65,255,$packman_pack_excludefiles).'</label></p>
	</fieldset>
	
	<p class="clear">
	<input type="submit" name="save_settings" value="'.__('save').'" /> 
	<input type="submit" name="reset_settings" value="'.__('Reset settings').'" />'.
	$core->formNonce().
	form::hidden(array('tab'),'settings').
	form::hidden(array('p'),'pacKman').'
	</p>
	</form>
	</div>';
}

# --BEHAVIOR-- packmanAdminTabs
$core->callBehavior('packmanAdminTabs',$core,$default_tab);

dcPage::helpBlock('pacKman');

echo '<hr class="clear"/><p class="right">
<a class="button" href="'.$p_url.'&amp;tab=setting">'.__('Settings').'</a> - 
pacKman - '.$core->plugins->moduleInfo('pacKman','version').'&nbsp;
<img alt="'.__('pacKman').'" src="index.php?pf=pacKman/icon.png" />
</p></body></html>';
?>