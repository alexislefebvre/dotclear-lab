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

if (!defined('DC_CONTEXT_ADMIN')){return;}

class libPackman
{
	public static function is_writable($path,$file)
	{
		return !(
			empty($path) || 
			empty($file) || 
			!is_writable(dirname($path.'/'.$file))
		);
	}

	public static function tab($modules,$type=null,$redir=null)
	{
		$type = $type == 'themes' ? 'themes' : 'plugins';

		echo 
		'<div class="multi-part" id="packman-'.$type.'" title="'.
		sprintf(__('Pack up %s'),__($type)).'">';

		if (isset($_REQUEST['packupdone']))
			echo '<p class="message">'.__('Package successfully created').'</p>';

		if (!empty($modules) && is_array($modules)) {
			echo
			'<form action="plugin.php" method="post">'.
			'<table class="clear"><tr>'.
			'<th class="nowrap">'.__('Name').'</th>'.
			'<th class="nowrap">'.__('Version').'</th>'.
			'<th class="nowrap">'.__('Id').'</th>'.
			'<th class="nowrap">'.__('Description').'</th>'.
			'<th class="nowrap">'.__('Author').'</th>'.
			'</tr>';

			foreach ($modules as $id => $module) {	
				echo
				'<tr class="line">'.
				'<td class="nowrap"><label class="classic">'.
				form::checkbox(array('modules['.html::escapeHTML($id).']'),1).
				__(html::escapeHTML($module['name'])).'</label></td>'.
				'<td class="nowrap">'.html::escapeHTML($module['version']).'</td>'.
				'<td class="nowrap">'.html::escapeHTML($id).'</td>'.
				'<td class="maximal">'.html::escapeHTML($module['desc']).'</td>'.
				'<td class="nowrap">'.html::escapeHTML($module['author']).'</td>'.
				'</tr>';
			}
			echo
			'</table>'.
			'<p><input type="hidden" name="p" value="pacKman" />'.
			'<input type="hidden" name="type" value="'.$type.'" />';

			if ($redir)
				echo '<input type="hidden" name="redir" value="'.html::escapeHTML($redir).'" />';

			echo
			'<input type="hidden" name="action" value="packup" />'.
			'<input type="submit" name="packup" value="'.__('Pack up selected modules').'" />'.
			$GLOBALS['core']->formNonce().'</p>'.
			'</form>';
		} else
			echo '<p><strong>'.__('No available module').'</strong></p>';

		echo '</div>';
	}

	public static function repo($modules,$type)
	{
		if (!in_array($type,array('plugins','themes','repository'))) return;

		echo '<h2>'.sprintf(__('Package available in %s directory'),__($type)).'</h2>';

		if (isset($_REQUEST['deletedone']) && $_REQUEST['deletedone'] == $type)
			echo '<p class="message">'.__('Package successfully deleted').'</p>';

		if (isset($_REQUEST['copydone']) && $_REQUEST['copydone'] == $type)
			echo '<p class="message">'.__('Package successfully copied').'</p>';

		if (isset($_REQUEST['movedone']) && $_REQUEST['movedone'] == $type)
			echo '<p class="message">'.__('Package successfully moved').'</p>';

		if (isset($_REQUEST['installdone']) && $_REQUEST['installdone'] == $type)
			echo '<p class="message">'.__('Package successfully installed').'</p>';

		if (empty($modules) || !is_array($modules))
			echo '<p><strong>'.__('No available module').'</strong></p>';

		else {
			$combo_action = array(__('delete')=>'delete');

			if ($type == 'plugins' || $type == 'themes')
				$combo_action[__('install')] = 'install';

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
			'<th class="nowrap">'.__('File').'</th>'.
			'<th class="nowrap">'.__('Name').'</th>'.
			'<th class="nowrap">'.__('Version').'</th>'.
			'<th class="nowrap">'.__('Id').'</th>'.
			'<th class="nowrap">'.__('Description').'</th>'.
			'<th class="nowrap">'.__('Author').'</th>'.
			'</tr>';

			foreach($modules AS $module) {
				echo
				'<tr class="line">'.
				'<td class="nowrap"><label class="classic" title="'.
				html::escapeHTML($module['root']).
				'">'.
				form::checkbox(array('modules['.html::escapeHTML($module['id']).']'),$module['root']).
				basename($module['root']).'</label></td>'.
				'<td class="nowrap">'.__(html::escapeHTML($module['name'])).'</td>'.
				'<td class="nowrap">'.html::escapeHTML($module['version']).'</td>'.
				'<td class="nowrap">'.html::escapeHTML($module['id']).'</td>'.
				'<td class="maximal">'.html::escapeHTML($module['desc']).'</td>'.
				'<td class="nowrap">'.html::escapeHTML($module['author']).'</td>'.
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
			$GLOBALS['core']->formNonce().
			'</p>'.
			'</div>'.
			'</form>';
		}
	}
}
?>