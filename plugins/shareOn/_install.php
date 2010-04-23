<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of shareOn, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

$new_version = $core->plugins->moduleInfo('shareOn','version');
$old_version = $core->getVersion('shareOn');

if (version_compare($old_version,$new_version,'>=')) return;

try {
	# Check DC version (dev on)
	if (!version_compare(DC_VERSION,'2.1.6','>='))
	{
		throw new Exception('Plugin called shareOn requires Dotclear 2.1.6 or higher.');
	}
	# Check DC version (new settings)
	if (version_compare(DC_VERSION,'2.2','>='))
	{
		throw new Exception('Plugin called shareOn requires Dotclear up to 2.2.');
	}

	# Setting
	$css = 
	".shareonentry ul { list-style: none; margin: 4px; padding: 0; } \n".
	".shareonentry ul li { display: inline; margin: 4px; padding: 0; } \n".
	"#sidebar .shareonwidget ul { list-style: none; margin: 4px; padding: 0; border: none; } \n".
	"#sidebar .shareonwidget ul li { margin: 4px; padding: 0; border: none; } \n";

	$s =& $core->blog->settings;
	$s->setNameSpace('shareOn');

	$s->put('shareOn_active',false,'boolean','Enable shareOn',false,true);
	$s->put('shareOn_style',$css,'string','Special ShareOn css',false,true);
	$s->put('shareOn_title','','string','Title of buttons bar',false,true);
	$s->put('shareOn_home_place','after','string','Where to place ShareOn bar on home page',false,true);
	$s->put('shareOn_cat_place','after','string','Where to place ShareOn bar on category page',false,true);
	$s->put('shareOn_tag_place','after','string','Where to place ShareOn bar on tag page',false,true);
	$s->put('shareOn_post_place','after','string','Where to place ShareOn bar on post page',false,true);

	$s->setNameSpace('system');

	# Version
	$core->setVersion('shareOn',$new_version);

	return true;
}
catch (Exception $e) {
	$core->error->add($e->getMessage());
}
return false;
?>