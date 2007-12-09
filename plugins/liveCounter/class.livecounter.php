<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Live Counter', a plugin for Dotclear 2            *
 *                                                             *
 *  Copyright (c) 2007                                         *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Live Counter' (see COPYING.txt);       *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

# Version 0.2 - 04/12/2007
# This class needs Clearbricks common modules
# http://clearbricks.org/

class liveCounter
{
	/**
	Count connected visitors
	
	@param	file		<b>string</b>		Data file name
	@param	timeout	<b>integer</b>		Timeout for each visit
	@param	readonly	<b>boolean</b>		Do not store data
	@return	<b>integer</b> Number of connected visitors
	*/
	public static function getConnected($file,$timeout,$readonly=false,&$changed=false)
	{
		static $data = false;
		static $written = false;
		
		if ($data === false) {
			# Creating live data file if it doesn't exist
			if (!is_file($file)) {
				if (function_exists('touch')) {
					@touch($file);
				} else {
					try {files::putContent($file,'');}
					catch (Exception $e) {return null;}
				}
				if (!is_file($file)) {
					return null;
				}
			}

			$data = file($file);
		}
		
		$c_uid = http::browserUID('liveCounter');
		$c_time = time();
		$ts = $c_time-$timeout*60;
		$c = 1; $res = '';
		
		foreach ($data as $k=>$v)
		{
			list($uid,$time) = explode(',',$v);
			
			if ($time < $ts || $uid == $c_uid) { # Expired data OR same user
				continue;
			}
			
			$res .= $data[$k]; $c++;
		}
		$res .= $c_uid.','.$c_time."\n";
		
		if (!($readonly || $written)) {
			try {files::putContent($file,$res);}
			catch (Exception $e) {return null;}
			$written = true;
		}
		
		if ($c != count($data)) {
			$changed = true;
		}
		
		return $c;
	}
}
?>
