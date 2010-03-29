<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of agora, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010- Osku ,Tomtom and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$version = $core->plugins->moduleInfo('agora','version');

if (version_compare($core->getVersion('agora'),$version,'>=')){
	return;
}

# --INSTALL AND UPDATE PROCEDURES--
$s = new dbStruct($core->con,$core->prefix);

$s->message
	->message_id			('bigint',	0,	false)
	->post_id				('bigint',	0,	false)
	->user_id				('varchar',	32,	false)
	->message_dt			('timestamp',	0,	false,	'now()')
	->message_tz			('varchar',	128,	false,	"'UTC'")
	->message_creadt		('timestamp',	0,	false,	'now()')
	->message_upddt		('timestamp',	0,	false,	'now()')
	->message_format		('varchar',	32,	false,	"'xhtml'")
	->message_content		('text',	0,	true,	null)
	->message_content_xhtml	('text',	0,	false)
	->message_notes		('text',	0,	true,	null)
	->message_words		('text',	0,	true,	null)
	->message_status		('smallint',	0,	true,	0)
	;

$s->message->primary	('pk_message','message_id');

$s->message->index	('idx_message_user_id',	'btree',	'user_id');
$s->message->index	('idx_message_post_id',	'btree',	'post_id');

$s->message->reference	('fk_message_user','user_id','user','user_id','cascade','cascade');
$s->message->reference	('fk_message_post','post_id','post','post_id','cascade','cascade');

$si = new dbStruct($core->con,$core->prefix);
$changes = $si->synchronize($s);

$s =& $core->blog->settings;
$s->setNameSpace('agora');
$s->put('agora_flag',false,'boolean','Agora activation flag',true,true);
$s->put('agora_announce',__('<p class="message">Welcome to the Agora.</p>'),'string','Agora announce',true,true);
$s->put('nb_message_per_feed',20,'integer','Number of messages on feeds',true,true);

$core->setVersion('agora',$version);
return true;
?>
