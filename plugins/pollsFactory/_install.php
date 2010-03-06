<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of pollsFactory, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

$new_version = $core->plugins->moduleInfo('pollsFactory','version');
$old_version = $core->getVersion('pollsFactory');

if (version_compare($old_version,$new_version,'>=')) return;

try {
	# Tables
	$s = new dbStruct($core->con,$core->prefix);

	# Table principale des sondages
	$s->pollsfact_p
		->poll_id ('bigint',0,false)
		->post_id ('bigint',0,false)
		->poll_type ('varchar',32,false,"'pollsfactory'")
		->poll_creadt ('timestamp',0,false,'now()')
		->poll_upddt ('timestamp',0,false,'now()')
		->poll_strdt ('timestamp',0,false,'now()')
		->poll_enddt ('timestamp',0,false,'now()')
		->poll_status('smallint',0,false,"'-2'")

		->primary('pk_pollfact','poll_id')
		->unique('uk_pollfact_post_id','post_id')
		->index('idx_pollfact_poll_type','btree','poll_type');

	# Listes des questions d'un sondage
	$s->pollsfact_q
		->query_id ('bigint',0,false)
		->poll_id ('bigint',0,false)
		->query_type ('varchar',32,false)
		->query_title ('varchar',255,false)
		->query_desc ('text',0,false)
		->query_status('smallint',0,false,"'-2'")
		->query_pos('smallint',0,false,"'0'")

		->primary('pk_pollfact_query','query_id')
		->index('idx_pollfact_query_poll_id','btree','poll_id');

	# Listes des champs d'une questions
	$s->pollsfact_o
		->option_id ('bigint',0,false)
		->query_id ('bigint',0,false)
		->option_text ('text',0,false)
		->option_pos('smallint',0,false,"'0'")

		->primary('pk_pollfact_option','option_id')
		->index('idx_pollfact_option_query_id','btree','query_id');

	# Reponse a une question
	$s->pollsfact_r
		->response_id ('bigint',0,false)
		->query_id ('bigint',0,false)
		->user_id ('bigint',0,false)
		->response_text ('text',0,false)
		->response_selected('smallint',0,false,"'0'")

		->primary('pk_pollfact_response','response_id')
		->index('idx_pollfact_response_query_id','btree','query_id')
		->index('idx_pollfact_response_user_id','btree','user_id');

	# User who participate (for IP vote)
	$s->pollsfact_u
		->user_id ('bigint',0,false)
		->poll_id ('bigint',0,false)
		->user_upddt ('timestamp',0,false,'now()')
		->user_ip('varchar',25,false)

		->primary('pk_pollfact_user','user_id')
		->index('idx_pollfact_response_poll_id','btree','poll_id')
		->index('idx_pollfact_response_user_ip','btree','user_ip');

	$si = new dbStruct($core->con,$core->prefix);
	$changes = $si->synchronize($s);

	# Settings
	$s =& $core->blog->settings;

	$s->setNameSpace('pollsFactory');
	$s->put('pollsFactory_active',false,'boolean','Enable extension',false,true);
	$s->put('pollsFactory_user_ident',0,'integer','User identification',false,true);
	$s->put('pollsFactory_public_show',true,'boolean','Show reponse when user votes',false,true);
	$s->put('pollsFactory_public_graph',true,'boolean','Use graphics for results',false,true);

	$s->put('pollsFactory_graph_trigger',0,'integer','Last change for graph settings',false,true);
	$s->put('pollsFactory_graph_path','','string','Path to store charts images',false,true);
	$s->put('pollsFactory_graph_width','300','integer','Graphic width',false,true);
	$s->put('pollsFactory_graph_ttcolor','#000000','string','Graphic title color',false,true);
	$s->put('pollsFactory_graph_txcolor','#404040','string','Graphic text color',false,true);
	$s->put('pollsFactory_graph_bgcolor','#FFFFFF','string','Graphic background color',false,true);
	$s->put('pollsFactory_graph_chcolor','#F8F8F8','string','Graphic chart color',false,true);
	$s->put('pollsFactory_graph_barcolor','#4D89F9','string','Graphic bar color',false,true);

	$s->put('pollsFactory_public_tpltypes',serialize(array('post')),'string','List of templates types for full description of polls',false,true);
	$s->setNameSpace('system');

	# Version
	$core->setVersion('pollsFactory',$new_version);

	return true;
}
catch (Exception $e) {
	$core->error->add($e->getMessage());
}
return false;
?>