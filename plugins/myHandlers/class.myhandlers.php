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
	
	private static $defaults = array(
		'post'=>array(
			'url'=>'post',
			'repr'=>'^%s/(.+)$',
			'handler'=>array('dcUrlHandlers','post')),
		'category'=>array(
			'url'=>'category',
			'repr'=>'^%s/(.+)$',
			'handler'=>array('dcUrlHandlers','category')),
		'archive'=>array(
			'url'=>'archive',
			'repr'=>'^%s(/.+)?$',
			'handler'=>array('dcUrlHandlers','archive')),
		'feed'=>array(
			'url'=>'feed',
			'repr'=>'^%s/(.+)$',
			'handler'=>array('dcUrlHandlers','feed')),
		'trackback'=>array(
			'url'=>'trackback',
			'repr'=>'^%s/(.+)$',
			'handler'=>array('dcUrlHandlers','trackback'))
	);
	
	public static function overrideHandler($name,$url)
	{
		global $core;
		
		if (!isset(self::$defaults[$name])) {
			return;
		}
		
		$core->url->register($name,$url,
			sprintf(self::$defaults[$name]['repr'],$url),
			self::$defaults[$name]['handler']);
		
		if ($name == 'post') {
			$core->setPostType('post','post.php?id=%d',$core->url->getBase('post').'/%s');
		}
	}
	
	public static function getDefaults()
	{
		$res = array();
		foreach (self::$defaults as $name=>$handler)
		{
			$res[$name] = $handler['url'];
		}
		return $res;
	}
}
?>