<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcQRcode, a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

$new_version = $core->plugins->moduleInfo('dcQRcode','version');
$old_version = $core->getVersion('dcQRcode');

if (version_compare($old_version,$new_version,'>=')) return;

try {
	# Is DC 2.1.5 ?
	if (!version_compare(DC_VERSION,'2.1.5','>=')) {

		throw new Exception('dcQRcode requires Dotclear 2.1.5');
	}
	# Database
	$s = new dbStruct($core->con,$core->prefix);
	$s->qrcode
		->blog_id ('varchar',32,false)
		->qrcode_id ('bigint',0,false)
		->qrcode_type('varchar',32,false)
		->qrcode_data ('text',0,false)
		->qrcode_size ('integer',3,false,128)
		->primary('pk_qrcode','qrcode_id')
		->index('idx_qrcode_blog_id','btree','blog_id')
		->index('idx_qrcode_type','btree','qrcode_type');

	$si = new dbStruct($core->con,$core->prefix);
	$changes = $si->synchronize($s);

	# Settings
	$core->blog->settings->setNameSpace('dcQRcode');
	$core->blog->settings->put('qrc_active',false,'boolean','Enable plugin',false,true);
	$core->blog->settings->put('qrc_use_mebkm',true,'boolean','Use MEBKM anchor',false,true);
	$core->blog->settings->put('qrc_img_size',128,'integer','Image size',false,true);
	$core->blog->settings->put('qrc_cache_use',true,'boolean','qrc_cache_use',false,true);
	$core->blog->settings->put('qrc_cache_path','','string','Custom cache path',false,true);
	$core->blog->settings->setNameSpace('system');

	# Version
	$core->setVersion('dcQRcode',$core->plugins->moduleInfo('dcQRcode','version'));

	return true;
}
catch (Exception $e) {
	$core->error->add($e->getMessage());
}
return false;
?>