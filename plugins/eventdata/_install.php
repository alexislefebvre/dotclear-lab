<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of eventdata, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

# Get new version
$new_version = $core->plugins->moduleInfo('eventdata','version');
$old_version = $core->getVersion('eventdata');
# Compare versions
if (version_compare($old_version,$new_version,'>=')) return;
# Install
try {
	# Check DC version (dev on) //2.1.6 due to datepicker
	if (!version_compare(DC_VERSION,'2.1.6','>=')) {
		throw new Exception('Plugin called eventdata requires Dotclear 2.1.5 or higher.');
	}
	# Check DC version (new settings)
	if (version_compare(DC_VERSION,'2.2','>=')) {
		throw new Exception('Plugin called eventdata requires Dotclear up to 2.2.');
	}
	# Database schema
	$s = new dbStruct($core->con,$core->prefix);
	$s->eventdata
		->post_id ('bigint',0,false)
		->eventdata_start ('timestamp',0,false,'now()')
		->eventdata_end ('timestamp',0,false,'now()')
		->eventdata_type('varchar',64,false)
		->eventdata_location('text','',true)
		->primary('pk_eventdata','eventdata_type','post_id','eventdata_start','eventdata_end')
		->index('idx_eventdata_post_id','btree','post_id')
		->index('idx_eventdata_event_type','btree','eventdata_type')
		->index('idx_eventdata_event_start','btree','eventdata_start')
		->index('idx_eventdata_event_end','btree','eventdata_end')
		->reference('fk_eventdata_post','post_id','post','post_id','cascade','cascade');
	# Schema installation
	$si = new dbStruct($core->con,$core->prefix);
	$changes = $si->synchronize($s);
	# Settings options
	$s = $core->blog->settings;
	$s->setNameSpace('eventdata');
	$s->put('eventdata_active',
		false,'boolean','eventdata plugin enabled',false,true);
	$s->put('eventdata_blog_menu',
		false,'boolean','eventdata icon on blog menu',false,true);
	$s->put('eventdata_public_active',
		false,'boolean','eventdata public page enabled',false,true);
	$s->put('eventdata_importexport_active',
		true,'boolean','Enabled import/export behaviors',false,true);
	# Settings templates
	$s->put('eventdata_tpl_title',
		'Events','string','Public page title',false,true);
	$s->put('eventdata_tpl_desc',
		'','string','Public page description',false,true);
	$s->put('eventdata_tpl_dis_bhv',
		false,'boolean','Disable public entry behavior',false,true);
	$s->put('eventdata_tpl_theme',
		'default','string','Public page template',false,true);
	$s->put('eventdata_tpl_cats',
		'','string','Redirected categories',false,true);
	$s->put('eventdata_no_cats',
		'','string','Unlisted categories',false,true);
	$s->setNameSpace('system');
	# Set version
	$core->setVersion('eventdata',$new_version);
	return true;
}
catch (Exception $e) {
	$core->error->add($e->getMessage());
	return false;
}
?>