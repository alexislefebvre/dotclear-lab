<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Google Stuff', a plugin for Dotclear 2              *
 *                                                             *
 *  Copyright (c) 2008                                         *
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

$this_version = $core->plugins->moduleInfo('googlestuff','version');
$installed_version = $core->getVersion('googlestuff');

if (version_compare($installed_version,$this_version,'>=')) {
	return;
}

$core->blog->settings->setNamespace('googlestuff');
$core->blog->settings->put('googlestuff_uacct','','string','Google Analytics PageTracker ID',true,true);
$core->blog->settings->put('googlestuff_verify','','string','Google Webmaster Tools Verify code',true,true);
$core->blog->settings->setNamespace('system');

$core->setVersion('googlestuff',$this_version);

return true;
?>