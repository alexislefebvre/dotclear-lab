<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Public Media.
# Copyright 2008 Moe (http://gniark.net/)
#
# Public Media is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# Public Media is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) { return; }

# On lit la version du plugin
$m_version = $core->plugins->moduleInfo('publicMedia','version');
 
# On lit la version du plugin dans la table des versions
$i_version = $core->getVersion('publicMedia');
 
# La version dans la table est supérieure ou égale à
# celle du module, on ne fait rien puisque celui-ci
# est installé
if (version_compare($i_version,$m_version,'>=')) {
	return;
}

# change namespace of settings
$cur = $core->con->openCursor($core->prefix.'setting');
$cur->setting_ns = 'publicmedia';
$cur->update('WHERE (setting_id LIKE \'publicmedia_page%\') '.
	'AND setting_ns = \'system\';');

# La procédure d'installation commence vraiment là
$core->setVersion('publicMedia',$m_version);
return true;
?>