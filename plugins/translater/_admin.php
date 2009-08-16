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

if (!defined('DC_CONTEXT_ADMIN')){return;}

# Additionals locales
__('translater');
__('Translate your Dotclear plugins and themes');

# Add auth perm
$core->auth->setPermissionType('translater',__('manage translations'));

# Admin menu
$_menu['Plugins']->addItem(
	__('Translater'),
	'plugin.php?p=translater','index.php?pf=translater/icon.png',
	preg_match('/plugin.php\?p=translater(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('admin,translater',$core->blog->id));

# Plugins tab
if ($core->blog->settings->translater_plugin_menu)
	$core->addBehavior('pluginsToolsTabs',
		array('translaterBehaviors','pluginsToolsTabs'));

#  Themes menu	
if ($core->blog->settings->translater_theme_menu)
	$core->addBehavior('adminCurrentThemeDetails',
		array('translaterBehaviors','adminCurrentThemeDetails'));

# Behaviors
class translaterBehaviors
{
	public static function pluginsToolsTabs($core)
	{
		echo 
		'<div class="multi-part" id="translater" title="'.
		__('Translate extensions').
		'">'.
		'<table class="clear"><tr>'.
		'<th>&nbsp;</th>'.
		'<th>'.__('Name').'</th>'.
		'<th class="nowrap">'.__('Version').'</th>'.
		'<th class="nowrap">'.__('Details').'</th>'.
		'<th class="nowrap">'.__('Author').'</th>'.
		'</tr>';
		$modules = $core->plugins->getModules();
		foreach ($modules as $name => $plugin) {
			echo
			'<tr class="line">'.
			'<td class="nowrap">'.
			'<a href="plugin.php?p=translater&amp;type=plugin&amp;module='.$name.'"'.
			' title="'.__('Translate this plugin').'">'.__($plugin['name']).'</a></td>'.
			'<td class="nowrap">'.$name.'</td>'.
			'<td class="nowrap">'.$plugin['version'].'</td>'.
			'<td class="maximal">'.$plugin['desc'].'</td>'.
			'<td class="nowrap">'.$plugin['author'].'</td>'.
			'</tr>';
		}
		echo '</table></div>';
	}

	public static function adminCurrentThemeDetails($core,$id,$infos)
	{
		$root = path::real($infos['root']);
		if ($id != 'default' && is_dir($root.'/locales') 
		 && $core->auth->check('translater,admin',$core->blog->id)) {
			return 
			'<p><a href="plugin.php?p=translater&amp;type=theme&amp;module='.$id.'"'.
			' class="button">'.__('Translate this theme').'</a></p>';
		}
	}
}
?>