<?php
# ***** BEGIN LICENSE BLOCK *****
# This is Smilies Manager, a plugin for DotClear. 
# Copyright (c) 2005 k-net. All rights reserved.
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

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

# On lit la version du plugin
$m_version = $core->plugins->moduleInfo('smiliesManager','version');
 
# On lit la version du plugin dans la table des versions
$i_version = $core->getVersion('smiliesManager');
 
# La version dans la table est supérieure ou égale à
# celle du module, on ne fait rien puisque celui-ci
# est installé
if (version_compare($i_version,$m_version,'>=')) {
	return;
}
 # Création du setting (s'il existe, il ne sera pas écrasé)
/*$settings = new dcSettings($core,null);
$settings->setNamespace('smiliesmanager');
$settings->put('smiliesmanager_admintoolbar',true,'boolean','smiliesmanager_smiliesontoolbar',false,true);
*/
# La procédure d'installation commence vraiment là
$core->setVersion('smiliesManager',$m_version);
return true;

?>