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
 
$m_version = $core->plugins->moduleInfo('templator','version');
 
$i_version = $core->getVersion('templator');
 
if (version_compare($i_version,$m_version,'>=')) {
	return;
}

$core->blog->settings->setNamespace('templator');
$s =& $core->blog->settings;
$s->put('templator_flag',false,'boolean','Templator activation flag',true,true);
$s->put('templator_files','','string','My own supplementary template files',true,true);
$s->put('templator_files_active','','string','My active supplementary template files',true,true);

$core->setVersion('templator',$m_version);
return true;
?>
