<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Bloc-Notes.
# Copyright 2008,2009 Moe (http://gniark.net/)
#
# Bloc-Notes is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# Bloc-Notes is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Icons (*.png) are from Tango Icon theme :
#	http://tango.freedesktop.org/Tango_Icon_Gallery
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) {return;}

# On lit la version du plugin
$m_version = $core->plugins->moduleInfo('blocNotes','version');
 
# On lit la version du plugin dans la table des versions
$i_version = $core->getVersion('blocNotes');
 
# La version dans la table est supérieure ou égale à
# celle du module, on ne fait rien puisque celui-ci
# est installé
if (version_compare($i_version,$m_version,'>=')) {
	return;
}

# encode settings to preserve new lines when editing about:config
if (version_compare($i_version,'1.0.2','<'))
{
	# per-blog setting
	$rs = $core->con->select('SELECT setting_value, setting_id, blog_id '.
	'FROM '.$core->prefix.'setting '.
	'WHERE setting_ns = \'blocnotes\' '.
	'AND (setting_id = \'blocNotes_text\');');
	
	while($rs->fetch())
	{
		$cur = $core->con->openCursor($core->prefix.'setting');
		$cur->setting_value = base64_encode($rs->setting_value);
		$cur->update('WHERE setting_ns = \'blocnotes\' '.
			'AND setting_id = \''.$rs->setting_id.'\''.
			'AND blog_id = \''.$rs->blog_id.'\';');
	}
	
	# users setting (global)
	$rs = $core->con->select('SELECT setting_value, setting_id, blog_id '.
	'FROM '.$core->prefix.'setting '.
	'WHERE setting_ns = \'blocnotes\' '.
	'AND (setting_id LIKE \'blocNotes_text_%\');');
	
	while($rs->fetch())
	{
		$cur = $core->con->openCursor($core->prefix.'setting');
		$cur->setting_value = base64_encode($rs->setting_value);
		$cur->update('WHERE setting_ns = \'blocnotes\' '.
			'AND setting_id = \''.$rs->setting_id.'\';');
	}
}

# La procédure d'installation commence vraiment là
$core->setVersion('blocNotes',$m_version);

return true;
?>