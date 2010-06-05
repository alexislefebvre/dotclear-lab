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
try
{
	# Check DC version
	if (version_compare(DC_VERSION,'2.2-beta','<'))
	{
		throw new Exception('fac requires Dotclear 2.2');
	}
	
	$tpltypes = array('post','tag','archive');
	
	$facformats = array(
		uniqid() => array(
			'name' => 'default',
			'dateformat' => '',
			'lineslimit' => '5',
			'linestitletext' => '%T',
			'linestitleover' => '%D',
			'linestitlelength' => '150',
			'showlinesdescription' => '0',
			'linesdescriptionlength' => '350',
			'linesdescriptionnohtml' => '1',
			'showlinescontent' => '0',
			'linescontentlength' => '350',
			'linescontentnohtml' => '1'
		),
		uniqid() => array(
			'name' => 'full',
			'dateformat' => '',
			'lineslimit' => '20',
			'linestitletext' => '%T',
			'linestitleover' => '%D - %E',
			'linestitlelength' => '',
			'showlinesdescription' => '1',
			'linesdescriptionlength' => '',
			'linesdescriptionnohtml' => '1',
			'showlinescontent' => '1',
			'linescontentlength' => '',
			'linescontentnohtml' => '1'
		)
	);
	
	# Settings
	$core->blog->settings->addNamespace('fac');
	$core->blog->settings->fac->put('fac_active',false,'boolean','Enabled fac plugin',false,true);
	$core->blog->settings->fac->put('fac_public_tpltypes',serialize($tpltypes),'string','List of templates types which used fac',false,true);
	$core->blog->settings->fac->put('fac_formats',serialize($facformats),'string','Formats of feeds contents',false,true);
	$core->blog->settings->fac->put('fac_defaultfeedtitle','%T','string','Default title of feed',false,true);
	$core->blog->settings->fac->put('fac_showfeeddesc',1,'boolean','Show description of feed',false,true);
	/*
	$core->blog->settings->fac->put('fac_dateformat','','string','Date format',false,true);
	$core->blog->settings->fac->put('fac_lineslimit',5,'integer','Number of entries to show',false,true);
	$core->blog->settings->fac->put('fac_linestitletext','%T','string','Title of entries',false,true);
	$core->blog->settings->fac->put('fac_linestitleover','%D - %E','string','Over title of entries',false,true);
	$core->blog->settings->fac->put('fac_linestitlelength',150,'integer','Maximum length of title of entries',false,true);
	$core->blog->settings->fac->put('fac_showlinesdescription',0,'boolean','Show description of entries',false,true);
	$core->blog->settings->fac->put('fac_linesdescriptionlength',350,'integer','Maximum length of description of entries',false,true);
	$core->blog->settings->fac->put('fac_linesdescriptionnohtml',1,'boolean','Remove html of description of entries',false,true);
	$core->blog->settings->fac->put('fac_showlinescontent',0,'boolean','Show content of entries',false,true);
	$core->blog->settings->fac->put('fac_linescontentlength',350,'integer','Maximum length of content of entries',false,true);
	$core->blog->settings->fac->put('fac_linescontentnohtml',1,'boolean','Remove html of content of entries',false,true);
	*/
	# Version
	$core->setVersion('fac',$new_version);
	# Ok
	return true;
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
	return false;
}
?>