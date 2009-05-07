<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Tweak URLs', a plugin for Dotclear 2              *
 *                                                             *
 *  Copyright (c) 2009                                         *
 *  xave and contributors.                                     *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'My Favicon' (see COPYING.txt);         *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

if (!defined('DC_CONTEXT_ADMIN')) exit;
global $core;

$this_version = $core->plugins->moduleInfo('tweakurls','version');
$installed_version = $core->getVersion('tweakurls');

if (version_compare($installed_version,$this_version,'>=')) {
	return;
}

$core->blog->settings->setNamespace('tweakurls');
$core->blog->settings->put('tweakurls_posturltransform','','string','determines posts URL type.',true,true);
$core->blog->settings->setNamespace('system');

$core->setVersion('tweakurls',$this_version);

return true;
?>