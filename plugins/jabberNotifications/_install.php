<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Jabber Notifications, a plugin for Dotclear.
# 
# Copyright (c) 2007,2008,2011 Alex Pirine <alex pirine.fr>, Olivier Tétard
# 
# Licensed under the GPL version 2.0 license.
# A copy is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$label = 'jabberNotifications';
$m_version = $core->plugins->moduleInfo($label,'version');
$i_version = $core->getVersion($label);

if (version_compare($i_version,$m_version,'>=')) {
	return;
}

# --INSTALL AND UPDATE PROCEDURES--

$core->blog->settings->addNamespace(strtolower($label));
$s = &$core->blog->settings->jabbernotifications;

# New install / update (just erase settings - but not their values)
$s->put('jn_serv','',
	'string','Host',false,true);
$s->put('jn_port',5222,
	'integer','Port',false,true);
$s->put('jn_con','',
	'string','Secure connection protocol',false,true);
$s->put('jn_user','',
	'string','Username used to connect to the jabber server',false,true);
$s->put('jn_pass','',
	'string','Password used to connect to the jabber server',false,true);
$s->put('jn_enab',false,
	'boolean','Enable',false,true);
$s->put('jn_gateway','',
	'string','HTTP Gateway',false,true);

# --SETTING NEW VERSION--

$core->setVersion($label,$m_version);
unset($label,$i_version,$m_version,$s,$si);
return true;
?>