<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of sofa, a plugin for Dotclear.
# 
# Copyright (c) 2010 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$version = $core->plugins->moduleInfo('sofa','version');
if (version_compare($core->getVersion('sofa'),$version,'>=')) {
	return;
}

$core->blog->settings->addNamespace('sofa');
$core->blog->settings->sofa->put('enable',false,'boolean','Sofa activation flag',false,true);
$core->blog->settings->sofa->put('css','','string','Custom CSS stylesheet',false,true);

$core->setVersion('sofa',$version);
return true;

?>