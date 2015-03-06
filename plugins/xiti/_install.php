<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of xiti, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2015 JC Denis and contributors
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

$new_version = $core->plugins->moduleInfo('xiti','version');
$old_version = $core->getVersion('xiti');

if (version_compare($old_version,$new_version,'>=')) return;

try {
	# Is DC 2.2 ?
	if (version_compare(DC_VERSION,'2.2-beta','<')) {
		throw new Exception('xiti requires Dotclear 2.2');
	}
	# Setting
	$core->blog->settings->addNamespace('xiti');
	$core->blog->settings->xiti->put('xiti_active',true,'boolean','Enable xiti',false,true);
	$core->blog->settings->xiti->put('xiti_serial','','string','xiti user accompte',false,true);
	$core->blog->settings->xiti->put('xiti_footer',true,'boolean','Add xiti to page footer',false,true);
	$core->blog->settings->xiti->put('xiti_image',0,'integer','Image style',false,true);

	# Version
	$core->setVersion('xiti',$new_version);
	return true;
}
catch (Exception $e) {
	$core->error->add($e->getMessage());
}
return false;