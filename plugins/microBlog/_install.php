<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Micro-Blogging, a plugin for Dotclear.
# 
# Copyright (c) 2009 Jeremie Patonnier
# jeremie.patonnier@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }
 
$new_version = $core->plugins->moduleInfo('microBlog', 'version');
 
$old_version = $core->getVersion('microBlog');

if (version_compare($old_version, $new_version, '>=')) {
	return;
}

// Creation de la BDD contenant les paramètres de connexions
$s = new dbStruct($core->con, $core->prefix);
 
$s->MB_services
	->id(     'char',     32, false, "00000000000000000000000000000000")
	->service('varchar', 255, false)
	->user(   'varchar', 255, false)
	->pwd(    'varchar', 255, false)
	->params( 'smallint',  0, false, 0)
	->primary('pk_id','id')
	->unique( 'uk_service','service','user');

$si = new dbStruct($core->con, $core->prefix);
$changes = $si->synchronize($s);

$core->setVersion('microBlog', $new_version);
return true;