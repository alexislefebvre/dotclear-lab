<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2.
#
# Copyright (c) 2003-2009 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear Mymeta plugin.
# Copyright (c) 2009 Bruno Hondelatte, and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# All rights reserved.
#
# Mymeta plugin for DC2 is free software; you can redistribute it and/or modify
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

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$version = $core->plugins->moduleInfo('mymeta','version');

if (version_compare($core->getVersion('mymeta'),$version,'>=')) {
	return;
}

$core->setVersion('mymeta',$version);

# Settings compatibility test
if (version_compare(DC_VERSION,'2.2-alpha','>=')) {
	$core->blog->settings->addNamespace('mymeta');
	$mymeta_settings =& $core->blog->settings->mymeta;
} else {
	$core->blog->settings->setNamespace('mymeta');
	$mymeta_settings =& $core->blog->settings;
}

if ($mymeta_settings->mymeta_fields == null)
	return true;

$fields = unserialize(base64_decode($mymeta_settings->mymeta_fields));
if (!is_array($fields) || count($fields)==0 )
	return true;
$ftest = each($fields);
if(get_class($ftest[1]) != 'stdClass')
	return true;

$mymeta = new mymeta($core,true);
foreach ($fields as $k => $v) {
	$newfield = $mymeta->newMyMeta($v->type);
	$newfield->id = $k;
	$newfield->enabled = $v->enabled;
	$newfield->prompt = $v->prompt;
	switch($v->type) {
		case 'list':$newfield->values = $v->values;;break;
	}
	$mymeta->update($newfield);
	
}
$mymeta->reorder();
$mymeta->store();

return true;
?>
