<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of fac, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

# Get new version
$new_version = $core->plugins->moduleInfo('fac','version');
$old_version = $core->getVersion('fac');

# Compare versions
if (version_compare($old_version,$new_version,'>=')){return;}

# Install or update
try {
	# Check DC version (dev on)
	if (!version_compare(DC_VERSION,'2.1.6','>='))
	{
		throw new Exception('Plugin called "fac" requires Dotclear 2.1.6 or higher.');
	}
	# Need metadata
	if (!$core->plugins->moduleExists('metadata'))
	{
		throw new Exception('Plugin called "fac" requires plugin "metadata".');
	}
	# Settings
	$s = facSettings($core);
	$s->put('fac_active',false,'boolean','Enabled fac plugin',false,true);
	$s->put('fac_public_tpltypes',serialize(array('post','tag','archive')),'string','List of templates types which used fac',false,true);
	$s->put('fac_defaultfeedtitle','%T','string','Default title of feed',false,true);
	$s->put('fac_showfeeddesc',1,'boolean','Show description of feed',false,true);
	$s->put('fac_dateformat','','string','Date format',false,true);
	$s->put('fac_lineslimit',5,'integer','Number of entries to show',false,true);
	$s->put('fac_linestitletext','%T','string','Title of entries',false,true);
	$s->put('fac_linestitleover','%D - %E','string','Over title of entries',false,true);
	$s->put('fac_linestitlelength',150,'integer','Maximum length of title of entries',false,true);
	$s->put('fac_showlinesdescription',0,'boolean','Show description of entries',false,true);
	$s->put('fac_linesdescriptionlength',350,'integer','Maximum length of description of entries',false,true);
	$s->put('fac_linesdescriptionnohtml',1,'boolean','Remove html of description of entries',false,true);
	$s->put('fac_showlinescontent',0,'boolean','Show content of entries',false,true);
	$s->put('fac_linescontentlength',350,'integer','Maximum length of content of entries',false,true);
	$s->put('fac_linescontentnohtml',1,'boolean','Remove html of content of entries',false,true);
	# Version
	$core->setVersion('fac',$new_version);
	# Ok
	return true;
}
catch (Exception $e) {
	$core->error->add($e->getMessage());
	return false;
}
?>