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
	// Tag
	$epcTag = array(
		'onEntryExcerpt' => false,
		'onEntryContent' => false,
		'onCommentContent' => false,
		'nocase' => false,
		'plural' => false,
		'style' => 'text-decoration: none; border-bottom: 3px double #CCCCCC;',
		'notag' => 'a,h1,h2,h3'
	);
	// Search
	$epcSearch = array(
		'onEntryExcerpt' => false,
		'onEntryContent' => false,
		'onCommentContent' => false,
		'nocase' => true,
		'plural' => true,
		'style' => 'color: #FFCC66;',
		'notag' => 'h1,h2,h3'
	);
	// Acronym
	$epcAcronym = array(
		'onEntryExcerpt' => false,
		'onEntryContent' => false,
		'onCommentContent' => false,
		'nocase' => false,
		'plural' => false,
		'style' => 'font-weight: bold;',
		'notag' => 'h1,h2,h3'
	);
	$epcAcronymList = array('DC'=>'Dotclear');
	// Link
	$epcLink = array(
		'onEntryExcerpt' => false,
		'onEntryContent' => false,
		'onCommentContent' => false,
		'nocase' => false,
		'plural' => false,
		'style' => 'text-decoration: none; font-style: italic; color: #0000FF;',
		'notag' => 'a,h1,h2,h3'
	);
	$epcLinkList = array('Dotaddict'=>'http://dotaddict.org');
	// Word
	$epcWord = array(
		'onEntryExcerpt' => false,
		'onEntryContent' => false,
		'onCommentContent' => false,
		'nocase' => false,
		'plural' => false,
		'style' => 'font-style: italic;',
		'notag' => 'h1,h2,h3'
	);
	$epcWordList = array('Fuck'=>'****');

	# Setting
	$s =& $core->blog->settings;
	$s->setNameSpace('enhancePostContent');

	$s->put('enhancePostContent_active',false,'boolean','Enable enhancePostContent',false,true);
	$s->put('enhancePostContent_Tag',serialize($epcTag),'string','Settings for tags features',false,true);
	$s->put('enhancePostContent_Search',serialize($epcSearch),'string','Settings for search features',false,true);
	$s->put('enhancePostContent_Acronym',serialize($epcAcronym),'string','Settings for acronym features',false,true);
	$s->put('enhancePostContent_AcronymList',serialize($epcAcronymList),'string','List of acronyms',false,true);
	$s->put('enhancePostContent_Link',serialize($epcLink),'string','Settings for word-to-link features',false,true);
	$s->put('enhancePostContent_LinkList',serialize($epcLinkList),'string','List of word-to-link',false,true);
	$s->put('enhancePostContent_Word',serialize($epcWord),'string','Settings for word-to-word features',false,true);
	$s->put('enhancePostContent_WordList',serialize($epcWordList),'string','List of word-to-word',false,true);

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