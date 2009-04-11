<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Log 404 Errors, a plugin for Dotclear 2
# Copyright (C) 2009 Moe (http://gniark.net/)
#
# Log 404 Errors is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# Log 404 Errors is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software Foundation,
# Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_RC_PATH')) {return;}

class log404Errors
{
	public function show($group = false)
	{
		global $core;

		if ($group)
		{
			$query = 'SELECT url, COUNT(id) AS count '.
				'FROM '.$core->prefix.'errors_log '.
				'GROUP BY url '.
				'ORDER BY count DESC;';
		}
		else
		{
			$query = 'SELECT id, url, dt, referrer '.
				'FROM '.$core->prefix.'errors_log '.
				'ORDER BY dt DESC;';
		}
		
		$rs = $core->con->select($query);
		
		while ($rs->fetch())
		{
			if ($group)
			{
				echo('<tr>'.
					'<td>'.$rs->count.'</td>'.
					'<td>'.$rs->url.'</td>'.
					'</tr>'."\n");
			}
			else
			{
				$referrer = $rs->referrer;
				
				if (empty($referrer))
				{
					$referrer = '&nbsp;';
				}
				else
				{
					$referrer = '<a href="'.html::escapeHTML($rs->referrer).'">'.
						text::cutString($rs->referrer,80).'</a>';
				}
				
				echo('<tr>'.
					'<td>'.$rs->id.'</td>'.
					'<td>'.html::escapeHTML($rs->url).'</td>'.
					'<td>'.dt::dt2str('%Y-%m-%d %H:%M:%S ',$rs->dt,
						$core->blog->settings->blog_timezone).'</td>'.
					'<td>'.$referrer.'</td>'.
					'</tr>'."\n");
			}
		}
	}
	
	public static function drop()
	{
		global $core;

		$query = 'DELETE FROM '.$core->prefix.'errors_log'.
		' WHERE (blog_id = \''.$core->con->escape($core->blog->id).'\');';

		$core->con->execute($query);
	}

}

?>