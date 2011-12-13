<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Sup Sub Tags.
# Copyright 2007,2009 Moe (http://gniark.net/)
#
# Sup Sub Tags is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# Sup Sub Tags is distributed in the hope that it will be useful,
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

if (!defined('DC_CONTEXT_ADMIN')) exit;
global $core;

# On lit la version du plugin
$m_version = $core->plugins->moduleInfo('supSubTags','version');
 
# On lit la version du plugin dans la table des versions
$i_version = $core->getVersion('supSubTags');
 
# La version dans la table est supérieure ou égale à
# celle du module, on ne fait rien puisque celui-ci
# est installé
if (version_compare($i_version,$m_version,'>=')) {return;}

# Création du setting
if (version_compare(str_replace("-r","-p",DC_VERSION),'2.2-alpha','>=')) {
	$core->blog->settings->addNamespace('supsubtags');
	$core->blog->settings->supsubtags->put('supsub_tags_sup_open','**','string',
		'Superscript open tag',false,true);
	$core->blog->settings->supsubtags->put('supsub_tags_sup_close','**','string',
		'Superscript close tag',false,true);
	$core->blog->settings->supsubtags->put('supsub_tags_sub_open',',,','string',
		'Subscript open tag',false,true);
	$core->blog->settings->supsubtags->put('supsub_tags_sub_close',',,','string',
		'Subscript close tag',false,true);
} else {

$settings = new dcSettings($core,$core->blog->id);
$settings->setNamespace('supsubtags');
$settings->put('supsub_tags_sup_open','**','string',
	'Superscript open tag',false,true);
$settings->put('supsub_tags_sup_close','**','string',
	'Superscript close tag',false,true);
$settings->put('supsub_tags_sub_open',',,','string',
	'Subscript open tag',false,true);
$settings->put('supsub_tags_sub_close',',,','string',
	'Subscript close tag',false,true);
$settings->setNamespace('system');
} 
# La procédure d'installation commence vraiment là
$core->setVersion('supSubTags',$m_version);
return true;
?>