<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of noodles, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

$new_version = $core->plugins->moduleInfo('noodles','version');
$old_version = $core->getVersion('noodles');

if (version_compare($old_version,$new_version,'>=')) return;

try
{
	if (version_compare(str_replace("-r","-p",DC_VERSION),'2.2-beta','<'))
	{
		throw new Exception('noodles requires Dotclear 2.2');
	}
	
	$core->blog->settings->addNamespace('noodles');
	
	$core->blog->settings->noodles->put('noodles_active',false,'boolean','Enable extension',false,true);
	
	$core->setVersion('noodles',$new_version);
	
	return true;
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
}
return false;
?>