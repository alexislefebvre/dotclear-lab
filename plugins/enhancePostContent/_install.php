<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of enhancePostContent, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

$new_version = $core->plugins->moduleInfo('enhancePostContent','version');
$old_version = $core->getVersion('enhancePostContent');

if (version_compare($old_version,$new_version,'>=')) return;

try {
	# Check DC version (dev on)
	if (!version_compare(DC_VERSION,'2.1.5','>='))
	{
		throw new Exception('Plugin called enhancePostContent requires Dotclear 2.1.5 or higher.');
	}
	# Check DC version (new settings)
	if (version_compare(DC_VERSION,'2.2','>='))
	{
		throw new Exception('Plugin called enhancePostContent requires Dotclear up to 2.2.');
	}

	# Prepare default values
	$styleTags = 'text-decoration: none; border-bottom: 3px double #CCCCCC;';
	$styleSearch = 'color: #FFCC66;';

	$styleAcronymes = 'font-weight: bold;';
	$listAcronymes = serialize(array('DC'=>'Dotclear'));

	$styleLinks = 'text-decoration: none; font-style: italic; color: #0000FF';
	$listLinks = serialize(array('Dotaddict'=>'http://dotaddict.org'));

	# Setting
	$s =& $core->blog->settings;
	$s->setNameSpace('enhancePostContent');

	$s->put('enhancePostContent_filterTags',false,'boolean','Filter tags in post content',false,true);
	$s->put('enhancePostContent_styleTags',$styleTags,'string','CSS for tags in post content',false,true);

	$s->put('enhancePostContent_filterSearch',false,'boolean','Filter search in post content',false,true);
	$s->put('enhancePostContent_styleSearch',$styleSearch,'string','CSS for search string in post content',false,true);

	$s->put('enhancePostContent_filterAcronymes',false,'boolean','Filter acronymes in post content',false,true);
	$s->put('enhancePostContent_styleAcronymes',$styleAcronymes,'string','CSS for acronymes in post content',false,true);
	$s->put('enhancePostContent_listAcronymes',$listAcronymes,'string','List of acronymes',false,true);

	$s->put('enhancePostContent_filterLinks',false,'boolean','Filter word to link in post content',false,true);
	$s->put('enhancePostContent_styleLinks',$styleLinks,'string','CSS for links in post content',false,true);
	$s->put('enhancePostContent_listLinks',$listLinks,'string','List of links',false,true);

	$s->setNameSpace('system');

	# Version
	$core->setVersion('enhancePostContent',$core->plugins->moduleInfo('enhancePostContent','version'));

	return true;
}
catch (Exception $e) {
	$core->error->add($e->getMessage());
}
return false;
?>