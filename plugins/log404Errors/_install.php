<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Log 404 Errors, a plugin for Dotclear 2
# Copyright (C) 2009 Moe (http://gniark.net/)
#
# Log 404 Errors is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# Log 404 Errors is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software Foundation,
# Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) {return;}

$m_version = $core->plugins->moduleInfo('log404Errors','version');

$i_version = $core->getVersion('log404Errors');
 
if (version_compare($i_version,$m_version,'>=')) {
	return;
}

$core->blog->settings->setNameSpace('log404errors');
$core->blog->settings->put(
	'log404errors_nb_per_page',
	30,
	'integer','Errors per page',true,true
);

# table
$s = new dbStruct($core->con,$core->prefix);

$s->errors_log
	->id('bigint',0,false)
	->blog_id('varchar',32,false)
	->url('text',0,false)
	->dt('timestamp',0,false,'now()')
	->ip('varchar',39,true,null)
	->referrer('text',0,true,null)
;

$si = new dbStruct($core->con,$core->prefix);
$changes = $si->synchronize($s);

$core->setVersion('log404Errors',$m_version);

return true;
?>