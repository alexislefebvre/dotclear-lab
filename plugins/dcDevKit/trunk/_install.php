<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcDevKit, a plugin for Dotclear.
# 
# Copyright (c) 2010 Tomtom, Dsls and contributors
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$m_version = $core->plugins->moduleInfo('dcDevKit','version');

$i_version = $core->getVersion('dcDevKit');

if (version_compare($i_version,$m_version,'>=')) {
	return;
}

$settings = new dcSettings($core,null);
$settings->addNamespace('dcDevKit');
# General settings
$settings->dcDevKit->put('author',$this->core->blog->settings->dcDevKit->author,'string','Author',false,true);
$settings->dcDevKit->put('licence','','string','Prefered licence',false,true);
# Packager settings
$settings->dcDevKit->put('packager_repository',$core->blog->public_path,'string','Repository path',false,true);
$settings->dcDevKit->put('packager_minify_js',false,'boolean','Minify *.js files',false,true);
$settings->dcDevKit->put('packager_minify_css',false,'boolean','Minify *.css files',false,true);
$settings->dcDevKit->put('packager_to_exclude','','string','Files/Folders to exclude',false,true);

$core->setVersion('dcDevKit',$m_version);

return true;

?>