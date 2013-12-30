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

$m_version = $core->plugins->moduleInfo('myfavicon','version');
$i_version = $core->getVersion('myfavicon');

if (version_compare($i_version,$m_version,'>=')) {
	return;
}

# Création du setting (s'il existe, il ne sera pas écrasé)
$settings = new dcSettings($core,null);
$settings->addNamespace('myfavicon');
$settings->myfavicon->put('favicon_url','','string','Favicon URL',false);
$settings->myfavicon->put('favicon_ie_url','','string','Favicon URL Internet Explorer',false);

# La procédure d'installation commence vraiment là
$core->setVersion('myfavicon',$m_version);
