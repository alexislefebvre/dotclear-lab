<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of DL Manager.
# Copyright 2008 Moe (http://gniark.net/) and Tomtom (http://blog.zenstyle.fr)
#
# DL Manager is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# DL Manager is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Images are from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) { return; }

# On lit la version du plugin
$m_version = $core->plugins->moduleInfo('dlManager','version');
 
# On lit la version du plugin dans la table des versions
$i_version = $core->getVersion('dlManager');
 
# La version dans la table est supérieure ou égale à
# celle du module, on ne fait rien puisque celui-ci
# est installé
if (version_compare($i_version,$m_version,'>=')) {
	return;
}

# change namespace of settings
$cur = $core->con->openCursor($core->prefix.'setting');
$cur->setting_ns = 'publicmedia';
$cur->update('WHERE (setting_id LIKE \'publicmedia_%\') '.
	'AND setting_ns = \'system\';');

# DL Manager
# rename Public Media settings
$core->con->execute('UPDATE '.$core->prefix.'setting SET '.
'setting_id = replace(setting_id,\'publicmedia_page\',\'dlmanager\') '.
'WHERE (setting_id LIKE \'publicmedia_%\');');

# change namespace of settings
$cur = $core->con->openCursor($core->prefix.'setting');
$cur->setting_ns = 'dlmanager';
$cur->update('WHERE setting_ns = \'publicmedia\';');


# move download counter to (dc_)media table
if (version_compare($i_version,'1.1-beta1','<'))
{
	# add media_download column to (dc_)media
	$s = new dbStruct($core->con,$core->prefix);
	$s->media->media_download('bigint',0,true,0);
	$si = new dbStruct($core->con,$core->prefix);
	$changes = $si->synchronize($s);
	
	// fixme : it works with only one blog
	$count_dl = unserialize($core->blog->settings->dlmanager_count_dl);
	if (is_array($count_dl))
	{
		foreach ($count_dl as $media_id => $dl)
		{
			$cur = $core->con->openCursor($core->prefix.'media');
			$cur->media_download = $dl;
			$cur->update('WHERE media_id = '.$media_id.';');		
		}
	}
	
	$core->blog->settings->drop('dlmanager_count_dl');
}

# La procédure d'installation commence vraiment là
$core->setVersion('dlManager',$m_version);
return true;
?>