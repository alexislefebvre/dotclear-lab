<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of clean:config.
# Copyright 2007 Moe (http://gniark.net/)
#
# clean:config is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# clean:config is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) and images are from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# ***** END LICENSE BLOCK *****

class cleanconfig
{

	public static function delete($setting,$limit)
	{
		global $core;

		if ($limit == 'blog')
		{
			$core->blog->settings->drop($setting);
		}
		elseif ($limit == 'global')
		{
			# inspirated from drop() function in /dotclear/inc/core/class.dc.settings.php
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
								$table->cell(__('namespace :').' <strong>'.$v['ns'].'</strong>','class="ns-name" colspan="5"');
								$echo_ns = true;
							}
							$table->row('class="line"');
							$table->cell(form::checkbox(array('settings[]',html::escapeHTML($k)),html::escapeHTML($k),false,$v['ns']));
							$table->cell('<label for="'.html::escapeHTML($k).'">'.$k.'</label>');
							# boolean
							if (($v['type']) == 'boolean')
							{
								$value = ($v['value']) ? 'true' : 'false';
							}
							#other types
							else
							{
								$value = form::field(html::escapeHTML($k.'_field'),40,
									null,html::escapeHTML($v['value']),null,null,null,'readonly="readonly"');
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
			'<input type="submit" name="delete" value="'.__('Remove selected settings').'" /></p>'."\n".
			'<p>'.$core->formNonce().'</p>');
		}
		$str .= '</form>'."\n";

		return($str);
	}

	public static function versions()
	{
		global $core;

		$table = new table('class="clear" summary="'.__('Settings').'"');

		$query = 'SELECT module, version FROM '.DC_DBPREFIX.'version WHERE (module != \'core\');';
		$rs = $core->con->select($query);

		# nothing to display
		if ($rs->isEmpty())
		{
			return('<p class="message">'.sprintf(__('%s is empty'),DC_DBPREFIX.'version').'</p>');
		}
		
		$str = '<form method="post" action="'.http::getSelfURI().'">'."\n";
		$table = new table('class="clear" summary="'.__('Versions').'"');
		$table->part('head');
		$table->row();
		$table->header(__('Module'),'colspan="2"');
		$table->header(__('Version'),'class="nowrap"');

		$table->part('body');

		while ($rs->fetch())
		{
			$module = $rs->module;
			$table->row('class="line"');
			$table->cell(form::checkbox(array('versions[]',html::escapeHTML($module)),html::escapeHTML($module)));
			$table->cell('<label for="'.html::escapeHTML(html::escapeHTML($module)).'">'.$module.'</label>');
			$table->cell($rs->version);
		}

		$str .= $table->get();
		$str .= ('<p class="checkboxes-helpers"></p>'.
			'<input type="submit" name="delete_versions" value="'.__('Remove selected versions').'" /></p>'."\n".
			'<p>'.$core->formNonce().'</p>');
		$str .= '</form>'."\n";

		return($str);
	}

	public static function delete_version($module)
	{
		global $core;

		# inspirated from drop() function in /dotclear/inc/core/class.dc.settings.php
		$strReq = 'DELETE FROM '.$core->prefix.'version ';
		$strReq .= 'WHERE module = \''.$core->con->escape($module).'\';';

		$core->con->execute($strReq);
	}
}

?>