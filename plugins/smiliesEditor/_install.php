<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of smiliesEditor, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { exit; }
 
$m_version = $core->plugins->moduleInfo('smiliesEditor','version');
 
$i_version = $core->getVersion('smiliesEditor');
 
if (version_compare($i_version,$m_version,'>=')) {
	return;
}

if (!version_compare(DC_VERSION,'2.1.6','<=')) { 
	$core->blog->settings->addNamespace('smilieseditor'); 
	$s =& $core->blog->settings->smilieseditor; 
} else { 
	$core->blog->settings->setNamespace('smilieseditor'); 
	$s =& $core->blog->settings; 
}

$s->put('smilies_bar_flag',false,'boolean','Show smilies toolbar',true,true);
$s->put('smilies_preview_flag',false,'boolean','Show smilies on preview',true,true);
$s->put('smilies_toolbar','','string','Smilies displayed in toolbar',true,true);

$core->setVersion('smiliesEditor',$m_version);
return true;
?>
