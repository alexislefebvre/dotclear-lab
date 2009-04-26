<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is not part of DotClear.
# Copyright (c) 2005 Alexandre LEGOUT aka LAlex and gtraxx. All rights
# reserved.
#
# dcGeshi is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# dcGeshi is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with dcGeshi; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# dcGeshi uses a free software under the GNU General Public License. See license
# infos on the class.dc.geshi.php file
#
# dcGeshi uses a free icon under the creativecommons. See license
# infos on the iconpack , http://www.clashdesign.net

# ***** END LICENSE BLOCK *****
if (!defined('DC_CONTEXT_ADMIN')) { exit; }

# On lit la version du plugin
$m_version = $core->plugins->moduleInfo('dcGeshi','version');
 
# On lit la version du plugin dans la table des versions
$i_version = $core->getVersion('dcGeshi');
 
# La version dans la table est supérieure ou égale à
# celle du module, on ne fait rien puisque celui-ci
# est installé
if (version_compare($i_version,$m_version,'>=')) {
	return;
}
# La procédure d'installation commence vraiment la
$core->setVersion('dcGeshi',$m_version);
return true;
?>