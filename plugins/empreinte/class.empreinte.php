<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Empreinte, a plugin for Dotclear.
# 
# Copyright (c) 2007,2008,2011 Alex Pirine <alex pirine.fr>
# 
# Licensed under the GPL version 2.0 license.
# A copy is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class empreinte
{
	private static $browsers = array(
		'Epiphany',
		'Firefox',
		'Flock',
		'Chrome',
		'Iceweasel',
		'Konqueror',
		'Links',
		'Lynx',
		'Minefield',
		'Minimo',
		'MSIE',
		'Netscape',
		'Opera',
		'Safari',
		'Seamonkey',
		'wget');
	
	private static $systems = array(
		'Android',
		'Blackberry',
		'BSD',
		'iPad',
		'iPhone',
		'iPod',
		'Java',
		'Linux',
		'Macintosh',
		'Nokia',
		'OS/2',
		'Palmos',
		'Playstation',
		'Smartphone',
		'SonyEricsson',
		'Sun',
		'Windows');
	
	public static function getUserAgentInfo(&$browser,&$system)
	{
		$browser = $system = '';
		$user_agent = @$_SERVER['HTTP_USER_AGENT'];
		if (empty($user_agent)) {
			return;
		}
		
		foreach (self::$browsers as $v)
		{
			if (preg_match('#'.$v.'#i',$user_agent)) {
				$browser = $v;
				break;
			}
		}
		
		foreach (self::$systems as $v)
		{
			if (preg_match('#'.$v.'#i',$user_agent)) {
				$system = $v;
				break;
			}
		}
	}
}
?>