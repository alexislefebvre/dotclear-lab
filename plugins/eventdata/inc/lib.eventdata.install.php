<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of eventdata, a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) return;

class eventdataInstall
{
	public static function pluginsBeforeDelete($plugin)
	{
		if($plugin['id'] == 'eventdata') {
			http::redirect('plugin.php?p=eventdata&t=uninstall');
			exit;
		}
	}

	public static function setTable(&$core)
	{
		# Database schema
		$s = new dbStruct($core->con,$core->prefix);
		$s->event
			->post_id ('bigint',0,false)
			->event_start ('timestamp',0,false,'now()')
			->event_end ('timestamp',0,false,'now()')
			->event_type('varchar',64,false)
			->primary('pk_event','event_type','post_id','event_start','event_end')
			->index('idx_event_post_id','btree','post_id')
			->index('idx_event_event_type','btree','event_type')
			->index('idx_event_event_start','btree','event_start')
			->index('idx_event_event_end','btree','event_end')
			->reference('fk_event_post','post_id','post','post_id','cascade','cascade');
		# Schema installation
		$si = new dbStruct($core->con,$core->prefix);
		$changes = $si->synchronize($s);
	}

	public static function delTable(&$core)
	{
		@$core->con->execute('TRUNCATE TABLE '.$core->con->escape($core->prefix.'event').'');
		@$core->con->execute('DROP TABLE '.$core->con->escape($core->prefix.'event').'');
	}

	public static function setSettings(&$core)
	{
		# Settings options
		$core->blog->settings->setNameSpace('eventdata');
		$core->blog->settings->put('event_option_active',false,'boolean','eventdata plugin enabled',false,true);
		$core->blog->settings->put('event_option_menu',false,'boolean','Icon place on admin menu',false,true);
		$core->blog->settings->put('event_option_public',false,'boolean','eventdata public page enabled',false,true);
		# Settings permissions
		$core->blog->settings->put('event_perm_pst',false,'boolean','Perm to manage events on entries',false,true);
		$core->blog->settings->put('event_perm_cat',false,'boolean','Perm to manage events categories',false,true);
		$core->blog->settings->put('event_perm_tpl',false,'boolean','Perm to manage events template',false,true);
		$core->blog->settings->put('event_perm_adm',false,'boolean','Perm to manage eventdata plugin',false,true);
		# Settings templates
		$core->blog->settings->put('event_tpl_title','Events','string','Public page title',false,true);
		$core->blog->settings->put('event_tpl_desc','','string','Public page description',false,true);
		$core->blog->settings->put('event_tpl_url','events','string','Public page default name',false,true);
		$core->blog->settings->put('event_tpl_dis_bhv',false,'boolean','Disable public entry behavior',false,true);
		$core->blog->settings->put('event_tpl_theme','default','string','Public page template',false,true);
		$core->blog->settings->put('event_tpl_cats','','string','Redirected categories',false,true);
	}

	public static function delSettings(&$core)
	{
		$core->con->execute('DELETE FROM '.$core->prefix.'setting WHERE setting_ns = \'eventdata\' ');
	}

	public static function setVersion(&$core)
	{
		$core->setVersion('eventdata',$core->plugins->moduleInfo('eventdata','version'));
	}

	public static function delVersion(&$core)
	{
		$core->delVersion('eventdata');
	}

	public static function delTemplates(&$core)
	{
		// Not yet
	}

	public static function delModule(&$core)
	{
		$core->plugins->deleteModule('eventdata');
	}
}
?>