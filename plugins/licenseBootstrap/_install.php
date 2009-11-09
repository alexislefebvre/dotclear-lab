<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of licenseBootstrap, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

$new_version = $core->plugins->moduleInfo('licenseBootstrap','version');
$old_version = $core->getVersion('licenseBootstrap');

if (version_compare($old_version,$new_version,'>=')) return;

try
{
	if (!version_compare(DC_VERSION,'2.1.5','>=')) {

		throw new Exception('licenseBootstrap plugin requires Dotclear 2.1.5');
	}
	$default_license = 'gpl2';
	$default_exts = licenseBootstrap::getDefaultExtensions();
	$default_headers = licenseBootstrap::getDefaultLicenses();

	$s =& $core->blog->settings;

	$s->setNamespace('licenseBootstrap');
	$s->put('licensebootstrap_addfull',true);
	$s->put('licensebootstrap_overwrite',false);
	$s->put('licensebootstrap_license',$default_license);
	$s->put('licensebootstrap_files_exts',
		licenseBootstrap::encode($default_exts));
	$s->put('licensebootstrap_licenses_headers',
		licenseBootstrap::encode($default_headers));
	$s->put('licensebootstrap_exclusion','/(\/locales\/)/');
	$s->put('licensebootstrap_packman_behavior',false);
	$s->put('licensebootstrap_translater_behavior',false);
	$s->setNamespace('system');

	$core->setVersion('licenseBootstrap',$new_version);

	return true;
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
}
return false;
?>