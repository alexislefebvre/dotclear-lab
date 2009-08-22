<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcMiniUrl, a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

# Get new version
$new_version = $core->plugins->moduleInfo('dcMiniUrl','version');
$old_version = $core->getVersion('dcMiniUrl');
# Compare versions
if (version_compare($old_version,$new_version,'>=')) {
	return;
} else {
	# Install
	try {
		$s = new dbStruct($core->con,$core->prefix);
		$s->miniurl
			->blog_id('varchar',32,false)
			->miniurl_type('varchar',64,false)
			->miniurl_id('varchar',32,false)
			->miniurl_str('varchar',255,false)
			->miniurl_dt('timestamp',0,false,'now()')
			->miniurl_counter('bigint',0,false,0)
			->miniurl_password('varchar',32,true)
			->primary('pk_shorturl','blog_id','miniurl_type','miniurl_id')
			->index('idx_shorturl_str','btree','miniurl_str')
			->index('idx_shorturl_type','btree','miniurl_type');

		$si = new dbStruct($core->con,$core->prefix);
		$changes = $si->synchronize($s);

		$core->blog->settings->setNameSpace('dcMiniUrl');
		$core->blog->settings->put('miniurl_active',
			false,'boolean','Enabled miniurl plugin',false,true);
		$core->blog->settings->put('miniurl_public_active',
			false,'boolean','Enabled miniurl public page',false,true);
		$core->blog->settings->put('miniurl_public_autoshorturl',
			false,'boolean','Enabled miniurl auto short url on public urls',false,true);
		$core->blog->settings->put('miniurl_blog_autoshorturl',
			false,'boolean','Enabled miniurl auto short url on contents',false,true);
		$core->blog->settings->put('miniurl_protocols',
			'http:,https:,ftp:,ftps:,irc:','string','Allowed miniurl protocols',false,true);
		$core->blog->settings->setNameSpace('system');

		$core->setVersion('dcMiniUrl',
			$core->plugins->moduleInfo('dcMiniUrl','version'));

		return true;
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
		return false;
	}
}
?>