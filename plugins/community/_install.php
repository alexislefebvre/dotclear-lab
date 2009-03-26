<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of community, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

# Version
$m_version = $core->plugins->moduleInfo('community','version');

$i_version = $core->getVersion('community');

if (version_compare($i_version,$m_version,'>=')) {
	return;
}

# Settings
$settings = new dcSettings($core,null);
$settings->setNamespace('community');
$settings->put('community_enabled',false,'boolean','Community enabled');
$settings->put('community_moderated',false,'boolean','Community moderated');
$settings->put('community_admin_email','','string','Community administrator email');
$settings->put('community_standby',serialize(array()),'string','Community standby users');
$settings->put('community_groups',serialize(array()),'string','Community users groups');

$core->setVersion('community',$m_version);

# Table
$s = new dbStruct($core->con,$core->prefix);
$s->community
	->group_id('smallint',0,false)
	->post_id('smallint',0,false)
	->blog_id('smallint',0,false)
	;
$s->community->reference('fk_community_post','post_id','post','post_id','cascade','cascade');
$s->community->reference('fk_community_blog','blog_id','blog','blog_id','cascade','cascade');
$si = new dbStruct($core->con,$core->prefix);
$changes = $si->synchronize($s);

?>