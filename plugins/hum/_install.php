<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of hum, a plugin for Dotclear 2.
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
$new_version = $core->plugins->moduleInfo('hum','version');
$old_version = $core->getVersion('hum');

# Compare versions
if (version_compare($old_version,$new_version,'>=')) {return;}

# Install or update
try {
	if (version_compare(DC_VERSION,'2.2-alpha','<')) {
		throw new Exception('Plugin called hum requires Dotclear 2.2 or higher.');
	}
	
	# Table
	$t = new dbStruct($core->con,$core->prefix);
	$t->comment->comment_selected('smallint',0,false,0);	
	$ti = new dbStruct($core->con,$core->prefix);
	$changes = $ti->synchronize($t);
	
	$css_extra = "#comments dt a.read-it { font-size: 0.8em; padding: 5px; font-style: italic; } ";
	
	# Settings
	$core->blog->settings->addNamespace('hum');
	$s = $core->blog->settings->hum;
	$s->put('active',false,'boolean','Enabled hum plugin',false,true);
	$s->put('comment_selected',false,'boolean','Select new comment by default',false,true);
	$s->put('jquery_hide',true,'boolean','Hide comments with jQuery fonction',false,true);
	$s->put('title_tag','dt','string','HTML tag of comment title block',false,true);
	$s->put('content_tag','dd','string','HTML tag of comment content block',false,true);
	$s->put('css_extra',$css_extra,'string','Additionnal style sheet',false,true);
	
	# Version
	$core->setVersion('hum',$new_version);
	
	return true;
}
catch (Exception $e) {
	$core->error->add($e->getMessage());
	return false;
}
?>