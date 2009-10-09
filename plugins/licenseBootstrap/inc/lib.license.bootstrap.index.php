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

class libLicenseBootstrap
{
	public static function tab($modules,$type=null,$redir=null)
	{
		if (!in_array($type,array('theme','plugin'))) return;

		echo 
		'<div class="multi-part" id="'.$type.'" title="'.__($type).'">';

		if (isset($_REQUEST['done']) && $_REQUEST['tab'] == $type)
			echo '<p class="message">'.__('License successfully included').'</p>';

		if (!empty($modules) && is_array($modules))
		{
			echo
			'<form action="plugin.php" method="post">'.
			'<table class="clear"><tr>'.
			'<th class="nowrap">'.__('Name').'</th>'.
			'<th class="nowrap">'.__('Version').'</th>'.
			'<th class="nowrap">'.__('Id').'</th>'.
			'<th class="nowrap">'.__('Description').'</th>'.
			'<th class="nowrap">'.__('Author').'</th>'.
			'</tr>';

			foreach ($modules as $id => $module)
			{	
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
			'<p><input type="hidden" name="p" value="licenseBootstrap" />'.
			'<input type="hidden" name="type" value="'.$type.'" />';

			if ($redir)
				echo '<input type="hidden" name="redir" value="'.html::escapeHTML($redir).'" />';

			echo
			'<input type="submit" name="add_license" value="'.__('Add license').'" />'.
			$GLOBALS['core']->formNonce().'</p>'.
			'</form>';
		}
		else
		{
			echo '<p><strong>'.__('No available module').'</strong></p>';
		}
		echo '</div>';
	}
}

?>