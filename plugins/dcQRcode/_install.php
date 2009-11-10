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
	$s = null;

	# Settings
	$s =& $core->blog->settings;
	$s->setNameSpace('dcQRcode');
	$s->put('qrc_active',false,'boolean','Enable plugin',false,true);
	$s->put('qrc_use_mebkm',true,'boolean','Use MEBKM anchor',false,true);
	$s->put('qrc_img_size',128,'integer','Image size',false,true);
	$s->put('qrc_cache_use',true,'boolean','qrc_cache_use',false,true);
	$s->put('qrc_cache_path','','string','Custom cache path',false,true);
	$s->put('qrc_nb_per_page',10,'integer','Number of records per page in admin',false,true);
	$s->put('qrc_api_url','http://chart.apis.google.com/chart?','string','',false,true);
	$s->put('qrc_api_ec_level','L','string','',false,true);
	$s->put('qrc_api_ec_margin',1,'integer','',false,true);
	$s->put('qrc_api_out_enc','UTF-8','string','',false,true);

	$s->setNameSpace('system');

	# Version
	$core->setVersion('dcQRcode',$new_version);

	return true;
}
catch (Exception $e) {
	$core->error->add($e->getMessage());
}
return false;
?>