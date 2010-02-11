<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of xiti, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
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
	# Is DC 2.1.5 ?
	if (!version_compare(DC_VERSION,'2.1.6','>=')) {
		throw new Exception('xiti requires Dotclear 2.1.6');
	}
	# Setting
	$s =& $core->blog->settings;
	$s->setNameSpace('xiti');
	$s->put('xiti_active',true,'boolean','Enable xiti',false,true);
	$s->put('xiti_serial','','string','xiti user accompte',false,true);
	$s->put('xiti_footer',true,'boolean','Add xiti to page footer',false,true);
	$s->put('xiti_image',0,'integer','Image style',false,true);
	$s->setNameSpace('system');
	# Version
	$core->setVersion('xiti',$new_version);
	return true;
}
catch (Exception $e) {
	$core->error->add($e->getMessage());
}
return false;
?>