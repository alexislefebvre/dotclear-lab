<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Grumph,
# a plugin for DotClear2.
#
# Copyright (c) 2010 Bruno Hondelatte and contributors
#
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/gpl-2.0.txt
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$version = $core->plugins->moduleInfo('grumph','version');

if (version_compare($core->getVersion('grumph'),$version,'>=')) {
	return;
}

/* Database schema
-------------------------------------------------------- */
$s = new dbStruct($core->con,$core->prefix);

$s->post
	->post_res	('text',		0,	true,	null)
	;
	
# Schema installation	
$si = new dbStruct($core->con,$core->prefix);
$changes = $si->synchronize($s);


$core->blog->settings->addNamespace('grumph');
$core->blog->settings->grumph->put('grumph_ajax_max_simul_updates',30,'integer','Max number of updates in one page');
$core->blog->settings->grumph->put('grumph_ajax_max_simul_queries',100,'integer','Max number of queries in one page');


$core->setVersion('grumph',$version);
return true;
?>
