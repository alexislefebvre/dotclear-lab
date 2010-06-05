<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Popularity Contest, a plugin for Dotclear 2
# Copyright (C) 2007,2009,2010 Moe (http://gniark.net/)
#
# Popularity Contest is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# Popularity Contest is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public
# License along with this program. If not, see
# <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) and images are from Silk Icons :
# <http://www.famfamfam.com/lab/icons/silk/>
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) {return;}

# On lit la version du plugin
$m_version = $core->plugins->moduleInfo('popularityContest','version');
 
# On lit la version du plugin dans la table des versions
$i_version = $core->getVersion('popularityContest');
 
# La version dans la table est supérieure ou égale à
# celle du module, on ne fait rien puisque celui-ci
# est installé
if (version_compare($i_version,$m_version,'>=')) {return;}

# default settings
$core->blog->settings->setNameSpace('popularitycontest');
# Time interval in seconds between sends to Popularity Contest : 3 days
$core->blog->settings->put('popularityContest_time_interval',
	(3*24*3600),'integer',
	'Time interval in seconds between sends to Popularity Contest',
	# don't replace old value, global setting
	false,true);
# Popularity Contest last report
$core->blog->settings->put('popularityContest_last_report',
	strtotime('-1 month'),'integer',
	'Popularity Contest last report (Unix timestamp)',false,true);
# Popularity Contest last try
$core->blog->settings->put('popularityContest_last_try',
	strtotime('-1 month'),'integer',
	'Popularity Contest last try (Unix timestamp)',false,true);
# Hide plugins
$core->blog->settings->put('popularityContest_hidden_plugins',
	base64_encode(serialize(array(''))),'text','Hidden plugins',false,true);
$core->blog->settings->setNameSpace('system');

# remove the file to force its update
if (file_exists(dirname(__FILE__).'/xml/plugins.xml'))
{
	unlink(dirname(__FILE__).'/xml/plugins.xml');
}

# La procédure d'installation commence vraiment là
$core->setVersion('popularityContest',$m_version);
return true;
?>