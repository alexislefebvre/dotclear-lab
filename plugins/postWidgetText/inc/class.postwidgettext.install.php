<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of postWidgetText a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

class postWidgetTextInstall
{
	public static function pluginsBeforeDelete($plugin)
	{
		if($plugin['id'] == 'postWidgetText') {
			http::redirect('plugin.php?p=postWidgetText');
			exit;
		}
	}

	public static function setTable($core)
	{
		$s = new dbStruct($core->con,$core->prefix);
		$s->post_wtext
			->post_id ('bigint',0,false)
			->wtext_type('varchar',64,false)
			->wtext_content('text','',true)
			->wtext_content_xhtml('text','',true)
			->wtext_words('text','',true)
			->primary('pk_post_wtext','wtext_type','post_id')
			->index('idx_post_wtext_post_id','btree','post_id')
			->index('idx_post_wtext_wtext_type','btree','wtext_type')
			->reference('fk_post_wtext_post','post_id','post','post_id','cascade','cascade');

		$si = new dbStruct($core->con,$core->prefix);
		$changes = $si->synchronize($s);
	}

	public static function delTable($core)
	{
		@$core->con->execute('TRUNCATE TABLE '.$core->con->escape($core->prefix.'post_wtext').'');
		@$core->con->execute('DROP TABLE '.$core->con->escape($core->prefix.'post_wtext').'');
	}

	public static function setSettings($core)
	{
		$core->blog->settings->setNameSpace('postwidgettext');
		$core->blog->settings->put('postwidgettext_active',true,'boolean','post widget text plugin enabled',false,true);
	}

	public static function delSettings($core)
	{
		$core->con->execute('DELETE FROM '.$core->prefix.'setting WHERE setting_ns = \'postwidgettext\' ');
	}

	public static function setVersion($core)
	{
		$core->setVersion('postWidgetText',$core->plugins->moduleInfo('postWidgetText','version'));
	}

	public static function delVersion($core)
	{
		$core->delVersion('postWidgetText');
	}

	public static function delModule($core)
	{
		$core->plugins->deleteModule('postWidgetText');
	}
}
?>