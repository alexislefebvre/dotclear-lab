<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is Jabber Notifications, a plugin for Dotclear 2      *
 *                                                             *
 *  Copyright (c) 2007,2008                                    *
 *  Oleksandr Syenchuk                                         *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along Jabber Notifications (see COPYING.txt);      *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$label = 'jabberNotifications';
$m_version = $core->plugins->moduleInfo($label,'version');
$i_version = $core->getVersion($label);

if (version_compare($i_version,$m_version,'>=')) {
	return;
}

# --INSTALL AND UPDATE PROCEDURES--

$sets = &$core->blog->settings;
$sets->setNamespace(strtolower($label));

# New install / update (just erase settings - but not their values)
$sets->put('jn_serv','',
	'string','Host',false,true);
$sets->put('jn_port',5222,
	'integer','Port',false,true);
$sets->put('jn_con','',
	'string','Secure connection protocol',false,true);
$sets->put('jn_user','',
	'string','Username used to connect to the jabber server',false,true);
$sets->put('jn_pass','',
	'string','Password used to connect to the jabber server',false,true);
$sets->put('jn_enab',false,
	'boolean','Enable',false,true);
$sets->put('jn_gateway','',
	'string','HTTP Gateway',false,true);

# --SETTING NEW VERSION--

$core->setVersion($label,$m_version);
unset($label,$i_version,$m_version,$s,$si);
return true;
?>