<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2005 Olivier Meunier and contributors. All rights
# reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

// filtrage des droits
if (!defined('DC_CONTEXT_ADMIN')) exit;

// chargement des librairies
require_once dirname(__FILE__).'/class.plugin.php';
require_once dirname(__FILE__).'/class.modele.php';

// est-ce qu'on a besoin d'installer et est-ce qu'on peut le faire ?
// on vérifie qu'il s'agit bien d'une version plus récente
$versionnew = $core->plugins->moduleInfo(pluginNewsletter::pname(), 'version');
$versionold = $core->getVersion(pluginNewsletter::pname());
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
