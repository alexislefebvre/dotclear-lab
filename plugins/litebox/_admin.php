<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Lite Box', a plugin for Dotclear 2                *
 *                                                             *
 *  Copyright (c) 2007                                         *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Lite Box' (see COPYING.txt);           *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

$core->addBehavior('coreInitWikiPost',array('liteBox','coreInitWikiPost'));
$core->addBehavior('pluginsAfterDelete',array('liteBox','pluginsAfterDelete'));

class liteBox 
{
	public static function coreInitWikiPost(&$wiki2xhtml) 
	{
		$wiki2xhtml->registerFunction('url:litebox',array('liteBox','linkTransform'));
	}
	
	public static function linkTransform($url,$content)
	{
		if (!ereg('^(.+)[.](gif|jpg|jpeg|png)$',$url)) {
			return;
		}
		
		$url = substr($url,strpos($url,':')+1);
		
		if (!empty($_POST['post_lang'])) {
			$lang = $_POST['post_lang'];
		}
		elseif ($GLOBALS['core']->blog->settings->get('lang') !== null) {
			$lang = $GLOBALS['core']->blog->settings->lang;
		}
		else {
			$lang = 'en';
		}
		
		return array('url'=>$url,'lang'=>$lang.'" rel="lightbox');
	}
	
	public static function pluginsAfterDelete($plugin)
	{
		if ($plugin['id'] == 'litebox') {
			if (!files::deltree(DC_TPL_CACHE.DIRECTORY_SEPARATOR.'cbtpl')) {
				throw new Exception(__('To finish unistall, please delete the whole cache/cbtpl directory.'));
			}
		}
	}
}
?>
