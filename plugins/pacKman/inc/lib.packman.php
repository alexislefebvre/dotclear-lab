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

if (!defined('DC_CONTEXT_ADMIN')) {

	return null;
}

class libPackman
{
	public static function is_configured($core, $repo, $file_a, $file_b)
	{
		if (!is_dir(DC_TPL_CACHE) || !is_writable(DC_TPL_CACHE)) {
			$core->error->add(
				__('Cache directory is not writable.')
			);
		}
		if (!is_writable($repo)) {
			$core->error->add(
				__('Path to repository is not writable.')
			);
		}

		if (empty($file_a)) {
			$core->error->add(
				__('You must specify the name of package to export.')
			);
		}

		if (!is_writable(dirname($repo.'/'.$file_a))) {
			$core->error->add(
				__('Path to first export package is not writable.')
			);
		}

		if (!empty($file_b) 
		 && !is_writable(dirname($repo.'/'.$file_b))) {
			$core->error->add(
				__('Path to second export package is not writable.')
			);
		}

		return !$core->error->flag();
	}

	public static function is_writable($path, $file)
	{
		return !(
			empty($path) || 
			empty($file) || 
			!is_writable(dirname($path.'/'.$file))
		);
	}

	public static function modules($core, $modules, $type, $title)
	{
		$type = $type == 'themes' ? 'themes' : 'plugins';

		echo 
		'<div class="multi-part" '.
		'id="packman-'.$type.'" title="'.$title.'">'.
		'<h3>'.$title.'</h3>';

		if (empty($modules) && !is_array($modules)) {
			echo 
			'<p><strong>'.__('There are no modules.').'</strong></p>'.
			'<div>';

			return null;
		}

		echo
		'<form action="plugin.php" method="post">'.
		'<table class="clear"><tr>'.
		'<th class="nowrap">'.__('Id').'</th>'.
		'<th class="nowrap">'.__('Version').'</th>'.
		'<th class="nowrap maximal">'.__('Name').'</th>'.
		'<th class="nowrap">'.__('Root').'</th>'.
		'</tr>';
		
		foreach (self::sort($modules) as $id => $module) {	
			echo
			'<tr class="line">'.
			'<td class="nowrap"><label class="classic">'.
				form::checkbox(array('modules['.html::escapeHTML($id).']'), 1).
				html::escapeHTML($id).
			'</label></td>'.
			'<td class="nowrap count">'.
				html::escapeHTML($module['version']).
			'</td>'.
			'<td class="nowrap maximal">'.
				__(html::escapeHTML($module['name'])).
			'</td>'.
			'<td class="nowrap">'.
				dirname(path::real($module['root'], false)).
			'</td>'.
			'</tr>';
		}

		echo
		'</table>'.
		'<p class="checkboxes-helpers"></p>'.
		'<p>'.
		(!empty($_REQUEST['redir']) ?
			form::hidden(
				array('redir'),
				html::escapeHTML($_REQUEST['redir'])
			) : ''
		).
		form::hidden(array('p'),'pacKman').
		form::hidden(array('type'),$type).
		form::hidden(array('action'),'packup').
		'<input type="submit" name="packup" value="'.
		 __('Pack up selected modules').'" />'.
		$core->formNonce().'</p>'.
		'</form>'.

		'</div>';
	}
	
	public static function repository($core, $modules, $type, $title)
	{
		if (!in_array($type,array('plugins','themes','repository'))) {

			return null;
		}

		echo 
		'<div class="multi-part" '.
		'id="packman-repository-'.$type.'" title="'.$title.'">'.
		'<h3>'.$title.'</h3>';

		if (empty($modules) || !is_array($modules)) {
			echo 
			'<p><strong>'.__('There are no packages').'</strong></p>'.
			'</div>';

			return null;
		}

		$combo_action = array(__('delete')=>'delete');
		
		if ($type == 'plugins' || $type == 'themes') {
			$combo_action[__('install')] = 'install';
		}
		if ($type != 'plugins') {
			$combo_action[sprintf(__('copy to %s directory'),__('plugins'))] = 'copy_to_plugins';
			$combo_action[sprintf(__('move to %s directory'),__('plugins'))] = 'move_to_plugins';
		}
		if ($type != 'themes') {
			$combo_action[sprintf(__('copy to %s directory'),__('themes'))] = 'copy_to_themes';
			$combo_action[sprintf(__('move to %s directory'),__('themes'))] = 'move_to_themes';
		}
		if ($type != 'repository') {
			$combo_action[sprintf(__('copy to %s directory'),__('repository'))] = 'copy_to_repository';
			$combo_action[sprintf(__('move to %s directory'),__('repository'))] = 'move_to_repository';
		}

		echo 
		'<form action="plugin.php" method="post">'.
		'<table class="clear"><tr>'.
		'<th class="nowrap">'.__('Id').'</th>'.
		'<th class="nowrap">'.__('Version').'</th>'.
		'<th class="nowrap">'.__('Name').'</th>'.
		'<th class="nowrap">'.__('File').'</th>'.
		'</tr>';

		$dup = array();
		foreach(self::sort($modules) AS $module) {

			if (isset($dup[$module['root']])) {
				continue;
			}

			$dup[$module['root']] = 1;

			echo
			'<tr class="line">'.
			'<td class="nowrap"><label class="classic" title="'.
				html::escapeHTML($module['root']).'">'.
				form::checkbox(array('modules['.html::escapeHTML($module['id']).']'),$module['root']).
				html::escapeHTML($module['id']).
			'</label></td>'.
			'<td class="nowrap count">'.
				html::escapeHTML($module['version']).
			'</td>'.
			'<td class="nowrap maximal">'.
				__(html::escapeHTML($module['name'])).
			'</td>'.
			'<td class="nowrap">'.
				'<a class="packman-download" href="plugin.php?p=pacKman&amp;package='.basename($module['root']).'&amp;repo='.$type.'" title="'.__('Download').'">'.
				html::escapeHTML(basename($module['root'])).'</a>'.
			'</td>'.
			'</tr>';
		}

		echo
		'</table>'.
		'<div class="two-cols">'.
		'<p class="col checkboxes-helpers"></p>'.
		'<p class="col right">'.__('Selected modules action:').' '.
		form::combo(array('action'),$combo_action).
		'<input type="submit" name="packup" value="'.__('ok').'" />'.
		form::hidden(array('p'),'pacKman').
		form::hidden(array('tab'),'repository').
		form::hidden(array('type'),$type).
		$core->formNonce().
		'</p>'.
		'</div>'.
		'</form>'.
		'</div>';
	}

	protected static function sort($modules)
	{
		$sorter = array();
		foreach($modules as $id => $module) {
			$sorter[$id] = $id;
		}
		array_multisort($sorter, SORT_ASC, $modules);

		return $modules;
	}
}
