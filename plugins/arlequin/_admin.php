<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Arlequin', a plugin for Dotclear 2                *
 *                                                             *
 *  Copyright (c) 2007                                         *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Arlequin' (see COPYING.txt);           *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/
if (!defined('DC_CONTEXT_ADMIN')) { return; }

require dirname(__FILE__).'/_widgets.php';

$_menu['Plugins']->addItem(__('Theme switcher'),'plugin.php?p=arlequin',
	'index.php?pf=arlequin/icon.png',
	preg_match('/plugin.php\?p=arlequin(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('contentadmin',$core->blog->id));

class adminArlequin
{
	public static function getDefaults()
	{
		return array(
			'e_html'=>'<li><a href="%1$s%2$s%3$s">%4$s</a></li>',
			'a_html'=>'<li><strong>%4$s</strong></li>',
			's_html'=>'<ul>%2$s</ul>',
			'homeonly'=>false);
	}
	
	public static function loadSettings(&$settings,&$initialized)
	{
		global $core;
		
		$initialized = false;
		$mt_cfg = @unserialize($settings->get('mt_cfg'));
		$mt_exclude = $settings->get('mt_exclude');
	
		// Paramètres corrompus ou inexistants
		if ($mt_cfg === false ||
			$mt_exclude === null ||
			!(isset($mt_cfg['e_html']) &&
			isset($mt_cfg['a_html']) &&
			isset($mt_cfg['s_html']) &&
			isset($mt_cfg['homeonly'])))
		{
			$mt_cfg = adminArlequin::getDefaults();
			$settings->setnamespace('multitheme');
			$settings->put('mt_cfg',serialize($mt_cfg),'string','Arlequin configuration');
			$settings->put('mt_exclude','customCSS','string','Excluded themes');
			$initialized = true;
			$core->blog->triggerBlog();
		}
		
		return array($mt_cfg,$mt_exclude);
	}
}
?>
