<?php
# ***** BEGIN LICENSE BLOCK *****
#
# Tribune Libre is a small chat system for Dotclear 2
# Copyright (C) 2007  Antoine Libert
# 
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
# 
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# ***** END LICENSE BLOCK *****
if (!defined('DC_CONTEXT_ADMIN')) { exit; }

$version = $core->plugins->moduleInfo('tribune','version');

if (version_compare($core->getVersion('tribune'),$version,'>=')) {
	return;
}

/* Database schema
-------------------------------------------------------- */
$s = new dbStruct($core->con,$core->prefix);

$s->tribune
	->tribune_id('bigint',0,false)
	->blog_id('varchar',32,false)
 	->tribune_nick('varchar',255,false)
	->tribune_ip('varchar',15,false)
	->tribune_dt('timestamp',0,false,'now()')
	->tribune_msg('varchar',255,false)
	->tribune_state('smallint',0,false,1)

	->primary('pk_tribune','tribune_id')
	;

$s->tribune->index('idx_tribune_blog_id','btree','blog_id');
$s->tribune->reference('fk_tribune_blog','blog_id','blog','blog_id','cascade','cascade');

# Schema installation
$si = new dbStruct($core->con,$core->prefix);
$changes = $si->synchronize($s);

$core->setVersion('tribune',$version);
return true;
?>
