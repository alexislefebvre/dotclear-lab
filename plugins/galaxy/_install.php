<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear Galaxy plugin.
#
# Dotclear Galaxy plugin is free software: you can redistribute it
# and/or modify  it under the terms of the GNU General Public License
# version 2 of the License as published by the Free Software Foundation.
#
# Dotclear Galaxy plugin is distributed in the hope that it will be
# useful, but WITHOUT ANY WARRANTY; without even the implied warranty
# of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Dotclear Galaxy plugin.
# If not, see <http://www.gnu.org/licenses/>.
#
# Copyright (c) 2010 Mounir Lamouri.
# Based on the Dotclear metadata plugin by Olivier Meunier.
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$version = $core->plugins->moduleInfo('galaxy','version');

if (version_compare($core->getVersion('galaxy'),$version,'>=')) {
	return;
}

/* Database schema
-------------------------------------------------------- */
$s = new dbStruct($core->con,$core->prefix);

$s->galaxy
	->planet_id		('varchar',	255,	false)
	->post_id		('bigint',	0,	false)
	
	->primary('pk_galaxy','planet_id','post_id')
	;

$s->galaxy->index('idx_galaxy_post_id','btree','post_id');
$s->galaxy->reference('fk_galaxy_post','post_id','post','post_id','cascade','cascade');

# Schema installation
$si = new dbStruct($core->con,$core->prefix);
$changes = $si->synchronize($s);

$core->setVersion('galaxy',$version);
return true;
?>
