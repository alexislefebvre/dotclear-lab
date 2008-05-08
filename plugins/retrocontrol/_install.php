<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is Rétrocontrôle, a plugin for Dotclear.              *
 *                                                             *
 *  Copyright (c) 2006-2008                                    *
 *  Oleksandr Syenchuk, Alain Vagner and contributors.         *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with Rétrocontrôle (see COPYING.txt);        *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

$label = 'retrocontrol';
$m_version = $core->plugins->moduleInfo($label,'version');
$i_version = $core->getVersion($label);

if (version_compare($i_version,$m_version,'>=')) {
	return;
}

# --INSTALL AND UPDATE PROCEDURES--

$sets = &$core->blog->settings;
$sets->setNamespace(strtolower($label));

# New install / update (just erase settings - but not their values)
$core->blog->settings->put('rc_sourceCheck',false,'boolean','Check trackback source',false,true);
$core->blog->settings->put('rc_timeoutCheck',false,'boolean','Use disposable URL for trackbacks',false,true);
$core->blog->settings->put('rc_recursive',true,'boolean','Recursive filtering while checking source',false,true);
$core->blog->settings->put('rc_timeout',300,'integer','Trackback URL time life (in seconds)',false,true);

# --SETTING NEW VERSION--

$core->setVersion($label,$m_version);
unset($label,$i_version,$m_version,$s,$si);
return true;
?>