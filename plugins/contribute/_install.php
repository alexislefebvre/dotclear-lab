<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Contribute.
# Copyright 2008,2009 Moe (http://gniark.net/)
#
# Contribute is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# Contribute is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) is from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) {return;}

# On lit la version du plugin
$m_version = $core->plugins->moduleInfo('contribute','version');
 
# On lit la version du plugin dans la table des versions
$i_version = $core->getVersion('contribute');
 
# La version dans la table est supérieure ou égale à
# celle du module, on ne fait rien puisque celui-ci
# est installé
if (version_compare($i_version,$m_version,'>=')) {return;}

# default settings
$core->blog->settings->setNameSpace('contribute');
# Time interval in seconds between sends to Popularity Contest : 3 days
$core->blog->settings->put('contribute_help',
	base64_encode('<p>help</p>'),'string',
	'Contribute help',
	# don't replace old value, global setting
	false,true);
$core->blog->settings->setNameSpace('system');

# La procédure d'installation commence vraiment là
$core->setVersion('contribute',$m_version);
return true;
?>