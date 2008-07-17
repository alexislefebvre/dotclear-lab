<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'My URL handlers', a plugin for Dotclear 2         *
 *                                                             *
 *  Copyright (c) 2007-2008                                    *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'My URL handlers' (see COPYING.txt);    *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

class myUrlHandlers
{
	private $sets;
	private $handlers = array();
	
	private static $defaults = array();
	
	public static function init(&$core)
	{
		foreach ($core->url->getTypes() as $k=>$v)
		{
			if (empty($v['url'])) {
				continue;
			}

			$p = '/'.preg_quote($v['url'],'/').'/';
			$v['representation'] = preg_replace($p,'%s',$v['representation'],1,$c);
			
			if ($c) {
				self::$defaults[$k] = $v;
			}
		}
	}
	
	public static function overrideHandler($name,$url)
	{
		global $core;
		
		if (!isset(self::$defaults[$name])) {
			return;
		}
		
		$core->url->register($name,$url,
			sprintf(self::$defaults[$name]['representation'],$url),
			self::$defaults[$name]['handler']);
		
		if ($name == 'post') {
			$core->setPostType('post','post.php?id=%d',$core->url->getBase('post').'/%s');
		}
		
		if ($name == 'pages') {
			$core->setPostType('page','plugin.php?p=pages&act=page&id=%d',$core->url->getBase('pages').'/%s');
		}
	}
	
	public static function getDefaults()
	{
		$res = array();
		foreach (self::$defaults as $k=>$v)
		{
			$res[$k] = $v['url'];
		}
		return $res;
	}
}
?>