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

if (!defined('DC_CONTEXT_ADMIN')) return;

# Get new version
$new_version = $core->plugins->moduleInfo('eventdata','version');
$old_version = $core->getVersion('eventdata');
# Compare versions
if (version_compare($old_version,$new_version,'>=')) return;
# Install
try {
	eventdataInstall::setTable($core);
	eventdataInstall::setSettings($core);
	eventdataInstall::setVersion($core);

	# Move event old table to eventdata new table for plugin version < 0.3.4
	if (version_compare($old_version,'0.1','>') && version_compare($old_version,'0.3.5','<')) {

		$rs = $core->con->select('SELECT post_id,event_start,event_end,event_type FROM '.$core->prefix.'event ');
		if (!$rs->isEmpty())
		{
			$cur_eventdata = $core->con->openCursor($core->prefix.'eventdata');
			while ($rs->fetch()) {
				$cur_eventdata->post_id     = (integer) $rs->post_id;
				$cur_eventdata->eventdata_start     = (string) $rs->event_start;
				$cur_eventdata->eventdata_end     = (string) $rs->event_end;
				$cur_eventdata->eventdata_type     = (string) ($rs->event_type == 'event' ? 'eventdata' : $rs->event_type);
				$cur_eventdata->insert();
			}
			$core->con->execute('DELETE FROM '.$core->prefix.'event WHERE event_type = \'event\'');

			$core->blog->settings->setNameSpace('eventdata');
			$core->blog->settings->put('eventdata_option_active',$core->blog->settings->event_option_active,'boolean','eventdata plugin enabled',true,false);
			$core->blog->settings->put('eventdata_option_menu',$core->blog->settings->event_option_menu,'boolean','Icon place on admin menu',true,false);
			$core->blog->settings->put('eventdata_option_public',$core->blog->settings->event_option_public,'boolean','eventdata public page enabled',true,false);
			$core->blog->settings->put('eventdata_perm_pst',$core->blog->settings->event_perm_pst,'boolean','Perm to manage events on entries',true,false);
			$core->blog->settings->put('eventdata_perm_cat',$core->blog->settings->event_perm_cat,'boolean','Perm to manage events categories',true,false);
			$core->blog->settings->put('eventdata_perm_tpl',$core->blog->settings->event_perm_tpl,'boolean','Perm to manage events template',true,false);
			$core->blog->settings->put('eventdata_perm_adm',$core->blog->settings->event_perm_adm,'boolean','Perm to manage eventdata plugin',true,false);
			$core->blog->settings->put('eventdata_tpl_title',$core->blog->settings->event_tpl_title,'string','Public page title',true,false);
			$core->blog->settings->put('eventdata_tpl_desc','',$core->blog->settings->event_tpl_desc,'Public page description',true,false);
			$core->blog->settings->put('eventdata_tpl_url',$core->blog->settings->event_tpl_url,'string','Public page default name',true,false);
			$core->blog->settings->put('eventdata_tpl_dis_bhv',$core->blog->settings->event_tpl_dis_bhv,'boolean','Disable public entry behavior',true,false);
			$core->blog->settings->put('eventdata_tpl_theme',$core->blog->settings->event_tpl_theme,'string','Public page template',true,false);
			$core->blog->settings->put('eventdata_tpl_cats',$core->blog->settings->event_tpl_cats,'string','Redirected categories',true,false);
			$core->blog->settings->put('eventdata_no_cats',$core->blog->settings->event_no_cats,'string','Unlisted categories',true,false);

			$set = $core->blog->settings;
			$set->drop('event_option_active');
			$set->drop('event_option_menu');
			$set->drop('event_option_public');
			$set->drop('event_perm_pst');
			$set->drop('event_perm_cat');
			$set->drop('event_perm_tpl');
			$set->drop('event_perm_adm');
			$set->drop('event_tpl_title');
			$set->drop('event_tpl_desc');
			$set->drop('event_tpl_url');
			$set->drop('event_tpl_dis_bhv');
			$set->drop('event_tpl_theme');
			$set->drop('event_tpl_cats');
			$set->drop('event_no_cats');

			$set = new dcSettings($core,null);
			$set->drop('event_option_active');
			$set->drop('event_option_menu');
			$set->drop('event_option_public');
			$set->drop('event_perm_pst');
			$set->drop('event_perm_cat');
			$set->drop('event_perm_tpl');
			$set->drop('event_perm_adm');
			$set->drop('event_tpl_title');
			$set->drop('event_tpl_desc');
			$set->drop('event_tpl_url');
			$set->drop('event_tpl_dis_bhv');
			$set->drop('event_tpl_theme');
			$set->drop('event_tpl_cats');
			$set->drop('event_no_cats');

		}
		unset($rs,$res);
	}
}
catch (Exception $e) {
	$core->error->add($e->getMessage());
}
return true;
?>