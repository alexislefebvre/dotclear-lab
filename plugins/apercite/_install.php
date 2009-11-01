<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Apercite, a plugin for Dotclear.
# 
# Copyright (c) 2009 Francis Besset
# francis.besset@free.fr
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$version = $core->plugins->moduleInfo('apercite','version');
if (version_compare($core->getVersion('apercite'),$version,'>=')) {
	return;
}

$settings = new dcSettings($core,$core->blog->id);
$settings->setNamespace('apercite');

if(!$settings->get('apercite_enabled')) $settings->put('apercite_enabled',0,'boolean');
if(!$settings->get('apercite_size')) $settings->put('apercite_enabled','120x90');
if(!$settings->get('apercite_javascript')) $settings->put('apercite_enabled',1,'boolean');
if(!$settings->get('apercite_java')) $settings->put('apercite_enabled',1,'boolean');
if(!$settings->get('apercite_login')) $settings->put('apercite_enabled','');
if(!$settings->get('apercite_api_key')) $settings->put('apercite_enabled','');

if(!$settings->get('apercite_local_link')) $settings->put('apercite_local_link',1,'boolean');


if(!$settings->get('apercite_workers')) $settings->put('apercite_workers',serialize(array('.post-excerpt','.post-content')));


$core->setVersion('apercite',$version);
?>