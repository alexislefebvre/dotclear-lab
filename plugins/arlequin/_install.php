<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Arlequin, a plugin for Dotclear.
# 
# Copyright (c) 2007,2008,2011 Alex Pirine <alex pirine.fr>
# 
# Licensed under the GPL version 2.0 license.
# A copy is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$version = $core->plugins->moduleInfo('arlequin','version');
if (version_compare($core->getVersion('arlequin'),$version,'>=')) {
	return;
}

$core->blog->settings->addNamespace('arlequin');
$s = &$core->blog->settings->arlequin;
$s->put('config','','string','Arlequin configuration',false);
$s->put('exclude','','string','Excluded themes',false);

$core->setVersion('arlequin',$version);
return true;
?>