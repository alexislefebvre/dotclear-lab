<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of clean:config, a plugin for Dotclear 2
# Copyright (C) 2007,2009,2010 Moe (http://gniark.net/)
#
# clean:config is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# clean:config is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public
# License along with this program. If not, see
# <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) and images are from Silk Icons :
# <http://www.famfamfam.com/lab/icons/silk/>
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_RC_PATH')) {return;}

class cleanconfig
{

	public static function delete($namespace,$setting,$limit)
	{
		global $core;

		if ($limit == 'blog')
		{
			# Settings compatibility test
			if (version_compare(DC_VERSION,'2.2-alpha1','>=')) {
				$core->blog->settings->{$namespace}->drop($setting);
			} else {
				$core->blog->settings->drop($setting);
			}
		}
		elseif ($limit == 'global')
		{
			# inspired by drop() function in /dotclear/inc/core/class.dc.settings.php
			$strReq = 'DELETE FROM '.$core->prefix.'setting'.' ';
			$strReq .= 'WHERE blog_id IS NULL ';
			$strReq .= "AND setting_id = '".$core->con->escape($setting)."' ";
	
			$core->con->execute($strReq);
		}
		else
		{
			throw new Exception('no limit');
		}
	}

	public static function settings($limit)
	{
		global $core;

		$str = '<p>'.__('Use carefully. Only settings related to plugins can be deleted.').'</p>'."\n";
		$str .= '<form method="post" action="'.http::getSelfURI().'">'."\n";
		$table = new table('class="clear" summary="'.__('Settings').'"');
		$table->part('head');
		$table->row();
		$table->header(__('Setting'),'colspan="2"');
		$table->header(__('Value'),'class="nowrap"');
		$table->header(__('Type'),'class="nowrap"');
		$table->header(__('Description'),'class="maximal"');
		
		$table->part('body');

		$settings = array();

		# limit to blog
		if ($limit == 'blog')
		{
			$dump = $core->blog->settings->dumpSettings();
		}
		# global
		else
		{
			$dump = $core->blog->settings->dumpGlobalSettings();
		}

		foreach ($dump as $k => $v) {
			$settings[$v['ns']][$k] = $v;
		}
		
		ksort($settings);

		# number of settings
		$i = 0;
		foreach ($settings as $k => $v)
		{
			# echo namespace
			$echo_ns = false;
			# only settings related to plugins 
			if (($k != 'system') AND ($k != 'widgets'))
			{
				ksort($v);
				foreach ($v as $k => $v)
				{
					# hide deleted settings
					if (!((!empty($_POST['settings'])) AND (in_array($k,$_POST['settings']))))
					{
						# hide global settings on blog settings
						if ((($limit == 'global') AND ($v['global'])) OR (($limit == 'blog') AND (!$v['global'])))
						{
							$table->row();
							# echo namespace 
							if (!$echo_ns)
							{
								$table->row();
								$table->cell(__('namespace:').
									' <strong>'.$v['ns'].'</strong>',
									'class="ns-name" colspan="5"');
								$echo_ns = true;
							}
							
							$id = html::escapeHTML($v['ns'].'|'.$k);
							$table->row('class="line"');
							$table->cell(form::checkbox(array('settings[]',$id),
								$id,false,$v['ns']));
							$table->cell('<label for="'.$id.'">'.$k.'</label>');
							# boolean
							if (($v['type']) == 'boolean')
							{
								$value = ($v['value']) ? 'true' : 'false';
							}
							#other types
							else
							{
								$value = form::field(html::escapeHTML($k.'_field'),40,
									null,html::escapeHTML($v['value']),null,null,null,
									'readonly="readonly"');
							}
							$table->cell($value);
							$table->cell($v['type']);
							$table->cell($v['label'],'class="maximal"');
							
							$i++;
						}
					}
				}
			}
		}
		# nothing to display
		if ($i == 0)
		{
			return('<p><strong>'.__('No setting.').'</strong></p>');
		}

		$str.= $table->get();

		if ($i > 0)
		{
			$str .= ('<p class="checkboxes-helpers"></p>'.
			'<p>'.form::hidden(array('limit',$limit),$limit).
			'<input type="submit" name="delete" value="'.__('Delete selected settings').'" /></p>'."\n".
			'<p>'.$core->formNonce().'</p>');
		}
		$str .= '</form>'."\n";

		return($str);
	}
}

?>