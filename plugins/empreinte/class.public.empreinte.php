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

class publicEmpreinte
{
	public static $c_info = array();
	
	public static function publicBeforeCommentCreate(&$cur)
	{
		global $core;
		
		$no_empreinte = !empty($_POST['no_empreinte']);
		
		if (!empty($_POST['c_remember']))
		{
			$c_cookie = array(
				'name' => $cur->comment_author,
				'mail' => $cur->comment_email,
				'site' => $cur->comment_site,
				'no_empreinte'=>(integer) $no_empreinte);
			$c_cookie = serialize($c_cookie);
			setcookie('comment_info',$c_cookie,strtotime('+3 month'),'/');
		}
		
		if ($no_empreinte) {
			return;
		}
		
		empreinte::getUserAgentInfo($browser,$system);
		
		$cur->comment_browser = $browser;
		$cur->comment_system = $system;
	}
	
	public static function coreBlogGetComments(&$c_rs)
	{
		$ids = array();
		while ($c_rs->fetch())
		{
			if (!$c_rs->comment_trackback) {
				$ids[] = $c_rs->comment_id;
			}
		}
		if (empty($ids)) {
			return;
		}
		
		$ids = implode(', ',$ids);
		
		$strReq =
		'SELECT comment_id, comment_browser, comment_system '.
		'FROM '.$c_rs->core->prefix.'comment '.
		'WHERE comment_id  IN ('.$ids.')';
		$rs = $c_rs->core->con->select($strReq);
		
		while ($rs->fetch())
		{
			self::$c_info[$rs->comment_id] = array(
				'browser'=>$rs->comment_browser,
				'system'=>$rs->comment_system
				);
		}
		
		$c_rs->extend('rsExtCommentEmpreinte');
	}
}
?>
