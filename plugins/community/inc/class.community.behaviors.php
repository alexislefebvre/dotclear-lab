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

class communityBehaviors
{
	public static function groupsField(&$core)
	{
		$groups = array_merge(array(__('None' => ''),$core->blog->settings->community_groups));

		echo
			'<h3><label for="community">'.__('Community groups:').'</label></h3>'.
			'<div class="p">'.form::combo('community',$groups,'').'</div>';
	}

	public static function setGroups(&$core)
	{
	
	}

	public static function exportFull(&$core,&$exp)
	{
		$exp->exportTable('community');
	}

	public static function exportSingle(&$core,&$exp,$blog_id)
	{
		$exp->export('community',
			'SELECT * '.
			'FROM '.$core->prefix.'community '.
			'WHERE blog_id = '".$blog_id."'"
		);
	}
}

?>
