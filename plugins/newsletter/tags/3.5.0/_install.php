<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of Newsletter.
# Copyright (C) 2009 Benoit de Marne, and contributors. All rights
# reserved.
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 3
# of the License, or (at your option) any later version.

# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.

# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
#
# ***** END LICENSE BLOCK *****

// filtrage des droits
if (!defined('DC_CONTEXT_ADMIN')) exit;

// chargement des librairies
require_once dirname(__FILE__).'/class.plugin.php';
require_once dirname(__FILE__).'/class.modele.php';

// est-ce qu'on a besoin d'installer et est-ce qu'on peut le faire ?
// on vérifie qu'il s'agit bien d'une version plus récente
///* suppression de la gestion du versionning
$versionnew = $core->plugins->moduleInfo(pluginNewsletter::pname(), 'version');
$versionold = $core->getVersion(pluginNewsletter::pname());
//*/
if (version_compare($versionold, $versionnew, '>=')) return;
else
{
	// chargement des librairies
	require_once dirname(__FILE__).'/class.admin.php';
	if (adminNewsletter::Install())
	{
		$core->setVersion(pluginNewsletter::pname(), $versionnew);
		unset($versionnew, $versionold);
		return true;		
	}
	else
	{
		$core->error->add(__('Unable to install Newsletter.'));
		return false;
	}
}

?>
