<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of mkcompat, a plugin for Dotclear 2.
#
# Copyright (c) 2009 Dotclear Team and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

if (!empty($_POST)) {
	if ($_POST['action'] == 'Upgrade')
	{
		if ($_POST['type'] == 'theme')
			mkcompat::themeUpgrade($_POST['root']);

		if ($_POST['type'] == 'plugin')
			mkcompat::pluginUpgrade($_POST['root']);
			
		http::redirect($p_url.'&upd=1&type='.$_POST['type'].'&name='.$_POST['name']);
	}
}
?>
<html>
  <head>
    <title><?php echo __('Dotclear 2.1.6 compatibility plugin'); ?></title>
  </head>
  <body>
      <h1><?php echo __('Dotclear 2.1.6 compatibility plugin'); ?></h1>
<?php
if (!empty($_GET['upd'])) {
	if ($_GET['type'] == 'theme')
		echo '<p class="message">'.sprintf(__('The %s theme has been upgraded'),$_GET['name']).'</p>';
	if ($_GET['type'] == 'plugin')
		echo '<p class="message">'.sprintf(__('The %s plugin has been upgraded'),$_GET['name']).'</p>';
}
?>
      <h2><?php echo __('Important notice'); ?></h2>
      <p><?php echo __('This plugin tries to update your themes and plugins to work smoothly with the latest version of Dotclear. It is provided as-is, may not work, and may even break something. Please do a backup of your modules before using it.'); ?></p>
      <h2><?php echo __('Themes requiring an upgrade'); ?></h2>
<?php
	$core->themes = new dcThemes($core);
	$core->themes->loadModules($core->blog->themes_path,null);
	$themes = $core->themes->getModules();
	unset($themes['default']);
	
	foreach ($themes as $k => $v)
	{
		if (!mkcompat::themeNeedUpgrade($v['root'])) unset($themes[$k]);
	}
	
	if (count($themes) > 0)
	{
		echo '<table>';
		foreach ($themes as $k => $v)
		{
			echo '<tr><th title="'.$v['desc'].'">'.$v['name'].'</th>'.
				'<td>'.$v['author'].'</td>'.
				'<td>'.$v['version'].'</td>';
			
			if ($v['root_writable'])
			{
				echo '<td><form action="'.$p_url.'" method="post">'.
				form::hidden('root',$v['root']).
				form::hidden('type','theme').
				form::hidden('name',$v['name']).
				$core->formNonce().
				'<input type="submit" name="action" value="'.__('Upgrade').'"></input>'.
				'</form></td>';
			} else {
				echo '<td>'.__('You do not have sufficient rights to upgrade this theme.').'</td>';
			}
				
			echo '</tr>';
		}
		echo '</table>';
	}
	else
	{
		echo '<p>'.__('Upgrade does not seem to be required for any theme.').'</p>';
	}
?>
      <h2><?php echo __('Plugins requiring an upgrade'); ?></h2>
<?php
	$plugins = $core->plugins->getModules();
	
	foreach ($plugins as $k => $v)
	{
		if (!mkcompat::pluginNeedUpgrade($v['root'])) unset($plugins[$k]);
	}
	
	if (count($plugins) > 0)
	{
		echo '<table>';
		foreach ($plugins as $k => $v)
		{
			echo '<tr><th title="'.$v['desc'].'">'.$v['name'].'</th>'.
				'<td>'.$v['author'].'</td>'.
				'<td>'.$v['version'].'</td>';
			
			if ($v['root_writable'])
			{
				echo '<td><form action="'.$p_url.'" method="post">'.
				form::hidden('root',$v['root']).
				form::hidden('type','plugin').
				form::hidden('name',$v['name']).
				$core->formNonce().
				'<input type="submit" name="action" value="'.__('Upgrade').'"></input>'.
				'</form></td>';
			} else {
				echo '<td>'.__('You do not have sufficient rights to upgrade this plugin.').'</td>';
			}
				
			echo '</tr>';
		}
		echo '</table>';
	}
	else
	{
		echo '<p>'.__('Upgrade does not seem to be required for any plugin.').'</p>';
	}
?>
  </body>
</html>