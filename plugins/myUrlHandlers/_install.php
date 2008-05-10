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

$label = basename(dirname(__FILE__));
$m_version = $core->plugins->moduleInfo($label,'version');
$i_version = $core->getVersion($label);

if (version_compare($i_version,$m_version,'>=')) {
	unset($label,$m_version,$i_version); return;
}

$settings =& $core->blog->settings;
$settings->setNamespace(strtolower($label));
$settings->put('url_handlers','','string','Personalized URL handlers',false);

$core->setVersion($label,$m_version);
unset($label,$m_version,$i_version);
return true;
?>