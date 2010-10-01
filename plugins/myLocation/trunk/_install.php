<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of myLocation, a plugin for Dotclear.
#
# Copyright (c) 2010 Tomtom and contributors
# http://blog.zenstyle.fr/
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$m_version = $core->plugins->moduleInfo('myLocation','version');
$i_version = $core->getVersion('myLocation');

if (version_compare($i_version,$m_version,'>=')) {
	return;
}

$core->blog->settings->addNamespace('myLocation');
$core->blog->settings->myLocation->put('enable',false,'boolean','Enable myLocation',false,true);

# --INSTALL AND UPDATE PROCEDURES--
$s = new dbStruct($core->con,$core->prefix);

$s->comment
	->comment_location('varchar',255,false)
	;
$si = new dbStruct($core->con,$core->prefix);

try {
	$changes = $si->synchronize($s);
} catch (Exception $e) {
	$core->error->add($e);
}

$core->setVersion('myLocation',$m_version);

return true;

?>