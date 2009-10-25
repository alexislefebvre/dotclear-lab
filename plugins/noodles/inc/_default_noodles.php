<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of noodles, a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

require dirname(__FILE__).'/class.noodles.php';

global $__default_noodles;
$__default_noodles = new noodles();

# Posts (by public behavior)
$__default_noodles->add('posts',__('Entries'),'',array('othersNoodles','publicPosts'));
$__default_noodles->posts->size = 48;
$__default_noodles->posts->css = 'float:right;margin:4px;';

# Comments (by public behavior)
$__default_noodles->add('comments',__('Comments'),'',array('othersNoodles','publicComments'));
$__default_noodles->comments->active = true;
$__default_noodles->comments->size = 48;
$__default_noodles->comments->css = 'float:left;margin:4px;';

# Block with post title link (like homepage posts)
$__default_noodles->add('titlesposts',__('Entries titles'),array('genericNoodles','postURL'));
$__default_noodles->titlesposts->target = '.post-title a';
$__default_noodles->titlesposts->css = 'margin-right:2px;';

if ($core->plugins->moduleExists('widgets')) {

	# Widget Selected entries
	$__default_noodles->add('bestof',__('Selected entries'),array('genericNoodles','postURL'));
	$__default_noodles->bestof->target = '.selected li a';
	$__default_noodles->bestof->css = 'margin-right:2px;';

	# Widget Last entries
	$__default_noodles->add('lastposts',__('Last entries'),array('genericNoodles','postURL'));
	$__default_noodles->lastposts->target = '.lastposts li a';
	$__default_noodles->lastposts->css = 'margin-right:2px;';

	# Widget Last comments 
	$__default_noodles->add('lastcomments',__('Last comments'),array('widgetsNoodles','lastcomments'));
	$__default_noodles->lastcomments->active = true;
	$__default_noodles->lastcomments->target = '.lastcomments li a';
	$__default_noodles->lastcomments->css = 'margin-right:2px;';
}

# Plugin auhtorMode
if ($core->plugins->moduleExists('authorMode')
 && $core->blog->settings->authormode_active) {

	$__default_noodles->add('authorswidget',__('Authors widget'),array('authormodeNoodles','authors'));
	$__default_noodles->authorswidget->target = '#authors ul li a';
	$__default_noodles->authorswidget->css = 'margin-right:2px;';

	$__default_noodles->add('author',__('Author'),'',array('authormodeNoodles','author'));
	$__default_noodles->author->active = true;
	$__default_noodles->author->size = 48;
	$__default_noodles->author->target = '.dc-author #content-info h2';
	$__default_noodles->author->css = 'clear:left; float:left;margin-right:2px;';

	$__default_noodles->add('authors',__('Authors'),array('authormodeNoodles','authors'));
	$__default_noodles->authors->active = true;
	$__default_noodles->authors->size = 32;
	$__default_noodles->authors->target = '.dc-authors .author-info h2 a';
	$__default_noodles->authors->css = 'clear:left; float:left; margin:4px;';
}

# Plugin rateIt
if ($core->plugins->moduleExists('rateIt')
 && $core->blog->settings->rateit_active) {

	$__default_noodles->add('rateitpostsrank',__('Top rated entries'),array('genericNoodles','postURL'));
	$__default_noodles->rateitpostsrank->target = '.rateitpostsrank.rateittypepost ul li a'; // Only "post" type
	$__default_noodles->rateitpostsrank->css = 'margin-right:2px;';
}

# Plugin lastpostsExtend
if ($core->plugins->moduleExists('lastpostsExtend')) {

	$__default_noodles->add('lastpostsextend',__('Last entries (extend)'),array('genericNoodles','postURL'));
	$__default_noodles->lastpostsextend->target = '.lastpostsextend ul li a';
	$__default_noodles->lastpostsextend->css = 'margin-right:2px;';
}

# --BEHAVIOR-- initDefaultNoodles
$core->callBehavior('initDefaultNoodles',$__default_noodles);

?>