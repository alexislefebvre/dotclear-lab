<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of agora, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2012- Osku and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$version = $core->plugins->moduleInfo('agora','version');

if (version_compare($core->getVersion('agora'),$version,'>=')){
	return;
}
// Needed for old authenfication method (extension of dcAuth)
if (version_compare(DC_VERSION,'2.4.1.2','<')) {
	$core->error->add(sprintf(__('Dotclear version 2.4.1.2 minimum is required. Agora is deactivated')));
	$core->plugins->deactivateModule('agora');
	return false;
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
	->message_upddt			('timestamp',	0,	false,	'now()')
	->message_format		('varchar',	32,	false,	"'xhtml'")
	->message_content		('text',	0,	true,	null)
	->message_content_xhtml	('text',	0,	false)
	->message_notes			('text',	0,	true,	null)
	->message_words			('text',	0,	true,	null)
	->message_status		('smallint',	0,	true,	0)
	;

$s->message->primary('pk_message','message_id');
/* References indexes
-------------------------------------------------------- */
$s->message->index('idx_message_user_id',	'btree',	'user_id');
$s->message->index('idx_message_post_id',	'btree',	'post_id');
/* Performance indexes
-------------------------------------------------------- */
$s->message->index('idx_message_message_dt',			'btree',	'message_dt');
$s->message->index('idx_message_message_dt_message_id',		'btree',	'message_dt','message_id');
//$s->message->index		('idx_blog_post_post_dt_post_id',	'btree',	'blog_id','post_dt','post_id');
//$s->message->index		('idx_blog_post_post_status',		'btree',	'blog_id','post_status');
/* Foreign keys
-------------------------------------------------------- */
$s->message->reference('fk_message_user','user_id','user','user_id','cascade','cascade');
$s->message->reference('fk_message_post','post_id','post','post_id','cascade','cascade');

$si = new dbStruct($core->con,$core->prefix);
$changes = $si->synchronize($s);

$s =& $core->blog->settings;
$s->addNameSpace('agora');
// New settings
$s->agora->put('agora_flag',false,'boolean','Agora activation flag',true,true);
$s->agora->put('community_flag',false,'boolean','Community flag - people, profile',true,true);
$s->agora->put('wiki_flag',false,'boolean','Wiki flag - public entries edition',true,true);
$s->agora->put('user_desc',false,'boolean','Agora - users can change desc',true,true);
$s->agora->put('modify_pseudo',false,'boolean','Agora - users can change pseudo',true,true);

// Authenfication : 
$s->agora->put('register_flag',true,'boolean','Agora - register new user flag',true,true);
$s->agora->put('register_modo',false,'boolean','Agora - registration moderation',true,true);
$s->agora->put('recover_flag',true,'boolean','Agora - recover password flag',true,true);
$s->agora->put('private_flag',false,'boolean','Agora private flag',true,true);
// Main options : 
$s->agora->put('full_flag',false,'boolean','Messages or comments schema',true,true);
$s->agora->put('entry_excerpt',false,'boolean','Agora entry excerpt flag',true,true);
$s->agora->put('content_status',-2,'integer','Agora all new content default status',true,true);
$s->agora->put('content_syntax','wiki','string','Agora new content syntax globally defined',true,true);
$s->agora->put('new_post',false,'boolean','Accept new posts from registered user',true,true);
$s->agora->put('avatar',0,'integer','Handle a simple avatar mechanism for users',true,true);
// Hidden feature for now : Forum mechanism : update published date for active posts
// Tweaks :
# Display moderations links : boolean 
$s->agora->put('modo_links',true,'boolean','Display moderation links',true,true);
$s->agora->put('trig_date',false,'boolean','New message change published post date',true,true);
$s->agora->put('empty_category',false,'boolean','Agora - empty category',true,true);
$s->agora->put('nb_message_per_feed',20,'integer','Number of messages on feeds',true,true);
// about:config only: global auth works only on the same ndd.
$s->agora->put('global_auth',false,'boolean','Agora global multi blogs authenfication',true,true);

$core->setVersion('agora',$version);
return true;
?>
