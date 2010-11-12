<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of browsingHistory, a plugin for Dotclear 2.
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
$new_version = $core->plugins->moduleInfo('browsingHistory','version');
$old_version = $core->getVersion('browsingHistory');
# Compare versions
if (version_compare($old_version,$new_version,'>=')) return;
# Install
try
{
	# Check DC version
	if (version_compare(str_replace("-r","-p",DC_VERSION),'2.2-alpha','<'))
	{
		throw new Exception('browsingHistory requires Dotclear 2.2');
	}
	
	# Settings options
	$core->blog->settings->addNamespace('browsingHistory');
	$s = $core->blog->settings->browsingHistory;
	
	$more_css = 
	"div.browsinghistory-items { clear: both; margin: 0; padding: 10px; } \n".
	"div.browsinghistory-item { clear: left; margin: 10px 10px 20px 10px; } \n".
	"div.browsinghistory-item h4, div.browsinghistory-item p { padding-left: 52px; margin-bottom: 10px; } \n".
	"div.browsinghistory-item img { float: left; } \n";
	
	$s->put('on_footer',true,'boolean','Add history to footer',false,true);
	$s->put('mem_time',604800,'integer','Time to keep history',false,true);
	$s->put('lastn',5,'integer','Number of items to show',false,true);
	$s->put('more_css',$more_css,'string','Additionnal style sheet',false,true);
	$s->put('img_size','sq','string','Predefined image size',false,true);
	
	# Set version
	$core->setVersion('browsingHistory',$new_version);
	
	return true;
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
}
return false;
?>