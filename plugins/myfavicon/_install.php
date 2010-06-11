<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'My Favicon', a plugin for Dotclear 2              *
 *                                                             *
 *  Copyright (c) 2008                                         *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'My Favicon' (see COPYING.txt);         *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$label = 'myfavicon';
$m_version = $core->plugins->moduleInfo($label,'version');
$i_version = $core->getVersion($label);

if (version_compare($i_version,$m_version,'>=')) {
	return;
}

# --INSTALL AND UPDATE PROCEDURES--
if (version_compare(DC_VERSION,'2.2-beta1','<')) {
	$sets = &$core->blog->settings;
	$sets->setNamespace(strtolower($label));
	
	# New install / update
	$sets->put('favicon_url','','string','Favicon URL',false);
	$sets->put('favicon_ie_url','','string','Favicon URL Internet Explorer',false);
}
else {
	$core->blog->settings->addNamespace(strtolower($label));
	
	# New install / update
	$core->blog->settings->myfavicon->put('favicon_url','','string','Favicon URL',false);
	$core->blog->settings->myfavicon->put('favicon_ie_url','','string','Favicon URL Internet Explorer',false);
}
if (version_compare(DC_VERSION,'2.0-rc1','<')
&& file_exists(DC_TPL_CACHE.'/cbtpl')
&& !files::deltree(DC_TPL_CACHE.'/cbtpl')) {
	throw new Exception(__('To finish installation, please delete the whole cache/cbtpl directory.'));
}

# --SETTING NEW VERSION--

$core->setVersion($label,$m_version);
unset($label,$i_version,$m_version,$s,$si);
return true;
?>