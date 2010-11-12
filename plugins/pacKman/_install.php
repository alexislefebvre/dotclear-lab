<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of pacKman, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

$new_version = $core->plugins->moduleInfo('pacKman','version');
$old_version = $core->getVersion('pacKman');

if (version_compare($old_version,$new_version,'>=')) return;

try
{
	if (version_compare(str_replace("-r","-p",DC_VERSION),'2.2-alpha','<'))
	{
		throw new Exception('pacKman requires Dotclear 2.2');
	}
	
	$core->blog->settings->addNamespace('pacKman');
	$core->blog->settings->pacKman->put('packman_menu_plugins',false,'boolean','Add link to pacKman in plugins page',false,true);
	$core->blog->settings->pacKman->put('packman_pack_overwrite',false,'boolean','Overwrite existing package',false,true);
	$core->blog->settings->pacKman->put('packman_pack_filename','%type%-%id%','string','Name of package',false,true);
	$core->blog->settings->pacKman->put('packman_secondpack_filename','%type%-%id%-%version%','string','Name of second package',false,true);
	$core->blog->settings->pacKman->put('packman_pack_repository','','string','Path to package repository',false,true);
	$core->blog->settings->pacKman->put('packman_pack_excludefiles','*.zip,*.tar,*.tar.gz,.directory','string','Extra files to exclude from package',false,true);
	
	$core->setVersion('pacKman',$new_version);
	
	return true;
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
}
return false;
?>