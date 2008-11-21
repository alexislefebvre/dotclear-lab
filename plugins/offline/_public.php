<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Offline', a plugin for Dotclear 2                 *
 *                                                             *
 *  Copyright (c) 2008                                         *
 *  Osku and contributors.                                     *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Offline mode' (see LICENCE);           *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/
if (!defined('DC_RC_PATH')) { return; }

$core->tpl->addValue('OfflinePageTitle',array('tplOffline','OfflinePageTitle'));
$core->tpl->addValue('OfflineMsg',array('tplOffline','OfflineMsg'));

$core->addBehavior('publicBeforeDocument',array('urlOffline','offline'));

class urlOffline extends dcUrlHandlers
{
	public static function offline($args)
	{
		global $core;

		if ($core->blog->settings->blog_off_flag){
			$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
			self::serveDocument('offline.html');
			exit;
			}
		return;
	}
}

class tplOffline
{
	public static function OfflinePageTitle($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->settings->blog_off_page_title').'; ?>';
	}

	public static function OfflineMsg($attr)
	{
		return '<?php echo $core->blog->settings->blog_off_msg; ?>';
	}
}
?>
