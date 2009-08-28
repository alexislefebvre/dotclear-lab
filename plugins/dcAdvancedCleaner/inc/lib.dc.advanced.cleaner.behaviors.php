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

if (!defined('DC_ADMIN_CONTEXT')){return;}

class behaviorsDcAdvancedCleaner
{
	public static function pluginsBeforeDelete($plugin)
	{
		self::moduleBeforeDelete($plugin,'plugins.php?removed=1');
	}

	public static function themeBeforeDelete($theme)
	{
		self::moduleBeforeDelete($theme,'blog_theme.php?del=1');
	}

	public static function moduleBeforeDelete($module,$redir)
	{
		global $core;
		$done = false;

		if (!$core->blog->settings->dcadvancedcleaner_behavior_active) return;

		$uninstaller = new dcUninstaller($core);
		$uninstaller->loadModule($module['root']);

		$m_callbacks = $uninstaller->getDirectCallbacks($module['id']);
		$m_actions = $uninstaller->getDirectActions($module['id']);

		foreach($m_callbacks as $callback) {

			$f = unserialize(base64_decode($callback['func']));
			call_user_func($f,$module);
			$done = true;
		}

		foreach($m_actions as $type => $actions) {

			foreach($actions as $v) {

				$uninstaller->execute($type,$v['action'],$v['ns']);
				$done = true;
			}
		}

		if ($done) {
			http::redirect($redir);
		}
	}

	public static function dcAdvancedCleanerAdminTabs($core,$p_url)
	{
		self::modulesTabs($core,DC_PLUGINS_ROOT,$p_url.'&amp;t=uninstaller');
	}

	public static function pluginsToolsTabs($core)
	{
		self::modulesTabs($core,DC_PLUGINS_ROOT,'plugins.php?tab=uninstaller');
	}

	public static function modulesTabs($core,$path,$redir,$title='Uninstall extensions')
	{
		if (!$core->blog->settings->dcadvancedcleaner_behavior_active) return;

		$err = '';

		$uninstaller = new dcUninstaller($core);
		$uninstaller->loadModules($path);
		$modules = $uninstaller->getModules();
		$props = $uninstaller->getAllowedProperties();

		# Execute actions
		if (isset($_POST['action']) && $_POST['action'] == 'uninstall' && !empty($_POST['id'])) {

			foreach($_POST['id'] as $k => $id) {

				# Settings
				if (!isset($_POST['actions'][$k])) continue;

				try {
					foreach($_POST['actions'][$k] as $ks => $sentence) {

						$s = unserialize(base64_decode($sentence));

						if (!isset($s['type']) 
						 || !isset($s['action']) 
						 || !isset($s['ns'])) continue;

						$uninstaller->execute($s['type'],$s['action'],$s['ns']);

						http::redirect($_POST['redir'].'&removed=1');
					}
				}
				catch(Exception $e) {
					$err = $e->getMessage();
				}
			}
		}

		echo 
		'<div class="multi-part" id="uninstaller" title="'.
			__($title).'">';

		if($err)
			echo '<p class="error">'.$err.'</p>';

		if(!count($modules)) {
			echo '<p>'.__('There is no module with uninstall features').'</p></div>';
			return;
		}

		echo
		'<p>'.__('List of modules with advanced uninstall features').'</p>'.
		'<form method="post" action="'.$redir.'">'.
		'<table class="clear"><tr>'.
		'<th>&nbsp;</th>'.
		'<th>'.__('Module').'</th>';
		
		foreach($props as $pro_id => $prop) {
			echo '<th>'.__($pro_id).'</th>';
		}

		echo 
		'<th>'.__('extra').'</th>'.
		'</tr>';

		$i = 0;
		foreach($modules as $module_id => $module) {

			echo
			'<tr class="line">'.
			'<td class="nowrap"><label class="classic">'.
			form::checkbox(array('id['.$i.']'),$module_id).' '.$module_id.
			'</label></td>'.
			'<td class="maximal nowrap">'.$module['name'].' '.$module['version'].'</td>';

			$actions = $uninstaller->getUserActions($module_id);

			foreach($props as $prop_id => $prop) {
				echo '<td class="nowrap">';

				if (!isset($actions[$prop_id])) {
					echo '--</td>';
					continue;
				}

				$j = 0;
				foreach($actions[$prop_id] as $action_id => $action) {

					if (!isset($props[$prop_id][$action['action']])) continue;

					$ret = base64_encode(serialize(array(
						'type' => $prop_id,
						'action'=>$action['action'],
						'ns'=>$action['ns']
					)));

					echo '<label class="classic">'.
					form::checkbox(array('actions['.$i.']['.$j.']'),$ret).
					' '.$action['desc'].'</label><br />';

					$j++;
				}
				echo '</td>';
			}

			echo '<td class="nowrap">';

			$callbacks = $uninstaller->getUserCallbacks($module_id);

			if (empty($callbacks)) {
				echo '--';
			}

			$k = 0;
			foreach($callbacks as $callback_id => $callback) {

				$ret = base64_encode(serialize($callback['func']));

				echo '<label class="classic">'.
				form::checkbox(array('extras['.$i.']['.$k.']'),$ret).
				' '.$callback['desc'].'</label><br />';
			}

			echo '</td></tr>';
			$i++;
		}
		echo 
		'</table>'.
		'<p>'.
		$core->formNonce().
		form::hidden(array('action'),'uninstall').
		'<input type="submit" name="submit" value="'.__('Perform selected actions').'" /> '.
		'</p>'.
		'</form>'.
		'</div>';
	}
}
?>