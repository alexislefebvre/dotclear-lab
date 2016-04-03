<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Versions Manager, a plugin for Dotclear 2
# Copyright 2010-2015 Moe (http://gniark.net/)
#
# Versions Manager is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# Versions Manager is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public
# License along with this program. If not, see
# <http://www.gnu.org/licenses/>.
#
# ***** END LICENSE BLOCK *****

class versionsManager
{
	public static function versions()
	{
		global $core;
		
		$query = 'SELECT module, version '.
		'FROM '.DC_DBPREFIX.'version WHERE (module != \'core\');';
		$rs = $core->con->select($query);

		# nothing to display
		if ($rs->isEmpty())
		{
			return('<p class="message">'.__('no version').'</p>');
		}
		
		$table = new table('class="clear" summary="'.__('Versions').'"');
		$table->part('head');
		$table->row();
		$table->header(__('Delete'),'');
		$table->header(__('Module'),'');
		$table->header(__('Version'),'class="nowrap"');

		$table->part('body');

		while ($rs->fetch())
		{
			$module = $rs->module;
			$table->row('class="line"');
			$table->cell(form::checkbox(
			array('v['.$module.'][delete]','delete_'.$module),
				1,false,'delete_versions'));
			$table->cell('<label for="'.html::escapeHTML('delete_'.$module).'">'.$module.'</label>');
			$table->cell(
			form::field('v['.$module.'][version]',10,20,
					$rs->version));
		}

		return($table->get());
	}

	public static function delete_version($module)
	{
		global $core;

		# inspired by drop() function in /dotclear/inc/core/class.dc.settings.php
		$strReq = 'DELETE FROM '.$core->prefix.'version ';
		$strReq .= 'WHERE module = \''.$core->con->escape($module).'\';';

		$core->con->execute($strReq);
	}
	
	public static function update_version($module,$version)
	{
		global $core;
		
		$cur = $core->con->openCursor($core->prefix.'version');
		$cur->version = $version;
		$cur->update('WHERE (module = \''.$core->con->escape($module).'\')');
	}
}