<?php

# ***** BEGIN LICENSE BLOCK *****
# Widget SnapMe for DotClear.
# Copyright (c) 2007 Ludovic Toinel, All rights
# reserved.
#
# DotClear is free software; you can redistribute it and/or modify
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

if (!defined('DC_CONTEXT_ADMIN')) exit;

# On lit la version du plugin
$m_version = $core->plugins->moduleInfo('snapme','version');
 
# On lit la version du plugin dans la table des versions
$i_version = $core->getVersion('snapme');
 
# La version dans la table est supérieure ou égale à
# celle du module, on ne fait rien puisque celui-ci
# est installé
if (version_compare($i_version,$m_version,'>=')) {
	return;
}

# Synchronisation de la base 
$s = new dbStruct($core->con,$core->prefix);

$s->snapme
  ->id            ('bigint',      0,      false)
  ->pseudo        ('varchar',     128,    false)
  ->blog_url      ('varchar',     128,    false)
  ->ip            ('varchar',     16,     false)
  ->file_name     ('varchar',     32,     false)
  ->post_time     ('integer',     0,      false)
  ->primary       ('pk_snapme','id')
  ->index         ('idx_snapme_1','btree','pseudo')
  ->unique        ('uk_snapme_1','file_name')
  ->unique        ('uk_snapme_2','post_time');

$si = new dbStruct($core->con,$core->prefix);
$changes = $si->synchronize($s);

# Settings
//$settings = new dcSettings($core,null);
//$settings->setNamespace('snapme');
//$settings->put('title',true,'boolean','toto setting',false,true);
 
# FIn de la procédure d'installation 
$core->setVersion('snapme',$m_version);

return true;

?>
