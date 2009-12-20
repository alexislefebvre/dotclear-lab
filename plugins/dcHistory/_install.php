<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of dcHistory, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$version = $core->plugins->moduleInfo('dcHistory','version');

if (version_compare($core->getVersion('dcHistory'),$version,'>=')){
	return;
}

# --INSTALL AND UPDATE PROCEDURES--
$s = new dbStruct($core->con,$core->prefix);

$s->revision
	->post_id				('bigint',	0,	false)
	->user_id				('varchar',	32,	false)
	->revision_id			('bigint',	0,	false)
	->revision_dt			('timestamp',	0,	false,	'now()')
	->revision_tz			('varchar',	128,	false,	"'UTC'")
	->revision_content_diff		('text',	0,	true,	null)
	->revision_excerpt_diff		('text',	0,	true,	null)
	;

$s->revision->primary	('pk_revision','post_id','revision_id');

$s->revision->index	('idx_revision_user_id',	'btree',	'user_id');
$s->revision->index	('idx_revision_post_id',	'btree',	'post_id');

$s->revision->reference	('fk_revision_user','user_id','user','user_id','cascade','cascade');
$s->revision->reference	('fk_revision_post','post_id','post','post_id','cascade','cascade');

$si = new dbStruct($core->con,$core->prefix);
$changes = $si->synchronize($s);

$core->setVersion('dcHistory',$version);
return true;
?>