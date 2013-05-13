<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of licenseBootstrap, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2013 Jean-Christian Denis and contributors
# contact@jcdenis.fr
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

$rdc_version = '2.5-alpha';
$new_version = $core->plugins->moduleInfo('licenseBootstrap','version');
$old_version = $core->getVersion('licenseBootstrap');

if (version_compare($old_version,$new_version,'>=')) return;

try
{
	if (version_compare(str_replace("-r","-p",DC_VERSION),$rdc_version,'<')) {
		throw new Exception(sprintf('%s requires Dotclear %s','licenseBootstrap',$rdc_version));
	}
	
	$default_license = 'gpl2';
	$default_exts = licenseBootstrap::getDefaultExtensions();
	$default_headers = licenseBootstrap::getDefaultLicenses();

	$core->blog->settings->addNamespace('licenseBootstrap');
	$core->blog->settings->licenseBootstrap->put('licensebootstrap_addfull',true,'boolean','Add complete licence file',false,true);
	$core->blog->settings->licenseBootstrap->put('licensebootstrap_overwrite',false,'boolean','Overwrite existing licence',false,true);
	$core->blog->settings->licenseBootstrap->put('licensebootstrap_license',$default_license,'string','default licence',false,true);
	$core->blog->settings->licenseBootstrap->put('licensebootstrap_files_exts',licenseBootstrap::encode($default_exts),'string','List of files to include licenceEnable xiti',false,true);
	$core->blog->settings->licenseBootstrap->put('licensebootstrap_licenses_headers',licenseBootstrap::encode($default_headers),'string','File header licence text',false,true);
	$core->blog->settings->licenseBootstrap->put('licensebootstrap_exclusion','/(\/locales\/)/','string','Path to exlude',false,true);
	$core->blog->settings->licenseBootstrap->put('licensebootstrap_packman_behavior',false,'boolean','Add LicenceBootstrap to plugin pacKman',false,true);
	$core->blog->settings->licenseBootstrap->put('licensebootstrap_translater_behavior',false,'boolean','Add LicenceBootstrap to plugin translater',false,true);

	$core->setVersion('licenseBootstrap',$new_version);

	return true;
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
}
return false;
?>