<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Empreinte', a plugin for Dotclear 2               *
 *                                                             *
 *  Copyright (c) 2007,2008                                    *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Empreinte' (see COPYING.txt);          *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

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
		'Blackberry',
		'BSD',
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