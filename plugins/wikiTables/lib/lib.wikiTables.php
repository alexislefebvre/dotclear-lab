<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Wiki Tables, a plugin for Dotclear 2
# Copyright (C) 2009 Moe (http://gniark.net/)
#
# Wiki Tables is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# Wiki Tables is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software Foundation,
# Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
#
# Icon (icon.png) is from Silk Icons :
# http://www.famfamfam.com/lab/icons/silk/
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_RC_PATH')) {return;}

class wikiTables
{
	public static function transform($text,$args)
	{
		$table_extra_html = '';
		$caption = '';
		$content = '';
		
		$row_extra_html = '';
		
		$text = trim($text);
		
		if (!preg_match('/\{\|(.+?)\n(.+?)\n\|\}/msu',
			$text,$match))
		{
			return(__('invalid table'));
		}
		
		if (!empty($match[1]))
		{
			$table_extra_html = ''.$match[1];
		}
		
		$lines = explode("\n",$match[2]);
		
		$row = '';
		
		foreach ($lines as $line)
		{
			$line = trim($line);
			if (empty($line)) {continue;}
			
			$l2 = substr($line,0,2);
			$l1 = substr($l2,0,1);
			
			if ($l2 == '|-')
			{
				$end = substr($line,2);
				
				if (!empty($row))
				{
					$content .= '<tr '.$row_extra_html.'>'.$row.'</tr>'."\n";
				}
				
				$row = '';
				$row_extra_html = $end;
			}
			elseif ($l2 == '|+')
			{
				$end = substr($line,2);
				
				if (strpos($end,'|') !== false)
				{
					$explode = explode('|',$str,2);
					$caption = '<caption '.$explode[0].'>'.$explode[1].
						'</caption>';
				}
				else
				{
					$caption = '<caption>'.$end.'</caption>';
				}
			}
			elseif ($l1 == '!')
			{
				$end = substr($line,1);
				
				if (strpos($end,'!!') !== false)
				{
					$cells = explode('!!',$end);
					foreach ($cells as $cell)
					{
						$row .= self::th($cell);
					}
				}
				elseif (strpos($end,'||') !== false)
				{
					$cells = explode('||',$end);
					foreach ($cells as $cell)
					{
						$row .= self::th($cell);
					}
				}
				else
				{
					$row .= self::th($end);
				}
			}
			elseif ($l1 == '|')
			{
				$end = substr($line,1);
				
				if (strpos($end,'||') !== false)
				{
					$cells = explode('||',$end);
					foreach ($cells as $cell)
					{
						$row .= self::td($cell);
					}
				}
				else
				{
					$row .= self::td($end);
				}
			}
			else
			{
				$row .= $line;
				//throw new Exception(__('invalid line:').' '.$line);
			}
		}
		
		# if there is no '|-' at the end
		$content .= '<tr '.$row_extra_html.'>'.$row.'</tr>'."\n";
		
		return('<table'.$table_extra_html.'>'.
		$caption.
		$content.
		'</table>');
	}
	
	public static function th($str)
	{
		if (strpos($str,'!') !== false)
		{
			$explode = explode('!',$str,2);
			return('<th '.$explode[0].'>'.$explode[1].'</th>');
		}
		
		return('<th>'.$str.'</th>');
	}
	
	public static function td($str)
	{
		if (strpos($str,'|') !== false)
		{
			$explode = explode('|',$str,2);
			return('<td '.$explode[0].'>'.$explode[1].'</td>');
		}
		
		return('<td>'.$str.'</td>');
	}
}

?>