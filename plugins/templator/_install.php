<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of templator a plugin for Dotclear 2.
# 
# Copyright (c) 2010 Osku and contributors
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

if (version_compare(DC_VERSION,'2.2-beta','<'))
{
	$core->error->add(__('Version 2.2-beta of Dotclear at least is required for module Templator.'));
	$core->plugins->deactivateModule('templator');
	return false;
}

$new_version = $core->plugins->moduleInfo('templator','version');
 
$current_version = $core->getVersion('templator');
 
if (version_compare($current_version,$new_version,'>=')) {
	return;
}

$s =& $core->blog->settings->templator;
$s->put('templator_flag',false,'boolean','Templator activation flag',true,true);
$s->put('templator_files','','string','My own supplementary template files',true,true);

$core->setVersion('templator',$new_version);
return true;
?>