<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2.
#
# Copyright (c) 2003-2009 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$version = $core->plugins->moduleInfo('mymeta','version');

if (version_compare($core->getVersion('mymeta'),$version,'>=')) {
	return;
}

$core->setVersion('mymeta',$version);

if ($core->blog->settings->mymeta_fields == null)
	return true;

$fields = unserialize(base64_decode($core->blog->settings->mymeta_fields));
if (!is_array($fields) || count($fields)==0 )
	return true;
$ftest = each($fields);
if(get_class($ftest[1]) != 'stdClass')
	return true;

$mymeta = new mymeta($core,true);
foreach ($fields as $k => $v) {
	$newfield = $mymeta->newMyMeta($v->type);
	$newfield->id = $k;
	$newfield->enabled = $v->enabled;
	$newfield->prompt = $v->prompt;
	switch($v->type) {
		case 'list':$newfield->values = $v->values;;break;
	}
	$mymeta->update($newfield);
	
}
$mymeta->reorder();
$mymeta->store();

return true;
?>

