<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Super Admin, a plugin for Dotclear 2
# Copyright (C) 2009, 2011 Moe (http://gniark.net/)
#
# Super Admin is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# Super Admin is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software Foundation,
# Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
#
# Icon (icon.png) is from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) {return;}

# On lit la version du plugin
$m_version = $core->plugins->moduleInfo('superAdmin','version');
 
# On lit la version du plugin dans la table des versions
$i_version = $core->getVersion('superAdmin');
 
# La version dans la table est supérieure ou égale à
# celle du module, on ne fait rien puisque celui-ci
# est installé
if (version_compare($i_version,$m_version,'>=')) {return;}

# default settings
$core->blog->settings->addNamespace('superAdmin');
$core->blog->settings->superAdmin->put('enable_content_edition',
	false,'boolean',
	'Enable content edition of other blogs from the plugin',
	# don't replace old value, global setting
	false,true);

# La procédure d'installation commence vraiment là
$core->setVersion('superAdmin',$m_version);

return true;

?>