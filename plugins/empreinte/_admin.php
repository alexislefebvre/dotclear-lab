<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Empreinte', a plugin for Dotclear 2               *
 *                                                             *
 *  Copyright (c) 2007,2008,2011                               *
 *  Alex Pirine and contributors.                              *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Empreinte' (see COPYING.txt);          *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$_menu['Plugins']->addItem(
	'Empreinte','plugin.php?p=empreinte',
	'index.php?pf=empreinte/icon.png',
	preg_match('/plugin.php\?p=empreinte(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('usage,contentadmin',$core->blog->id)
);
$core->addBehavior('pluginsAfterDelete',array('adminEmpreinte','pluginsAfterDelete'));

$core->blog->settings->addNamespace('empreinte');

class adminEmpreinte
{
	public static function pluginsAfterDelete($plugin)
	{
		if ($plugin['id'] == 'empreinte') {
			if (!files::deltree(DC_TPL_CACHE.DIRECTORY_SEPARATOR.'cbtpl')) {
				throw new Exception(__('To complete plugin uninstall, please delete the whole cache/cbtpl directory.'));
			}
		}
	}
}
?>