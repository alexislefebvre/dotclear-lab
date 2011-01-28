<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of agora, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 Osku ,Tomtom and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

class agorapublicBehaviors
{
	public static function templateBeforeBlock($core,$b,$attr)
	{
		if (($core->auth->check('contentadmin',$core->blog->id))
		&& ($b == 'Entries') 
		&& in_array($core->url->type,array('agora','place','profile')))
		{
			return
			'<?php if ($core->auth->userID()) { '.
				"\$params['sql'] = 'AND post_status = 1 ';\n".
			"} ?>\n";
		}
	}
	
	// DOESN'T WORK
    public static function templateCustomSortByAlias($alias)
    {
		$alias = array(
			'message' => array(
				'author' => 'user_id',
				'date' => 'message_dt',
				'id' => 'message_id',
				'post_id' => 'post_id'
			)
		);
    }
	
	// => public
	public static function publicLoginFormAfter($core)
	{
		$res = '<p><a href="'.$core->blog->url.$core->url->getBase("recover").'/">'.__('I forgot my password').'</a></p>';
		$res .= '<p><a href="'.$core->blog->url.$core->url->getBase("register").'">'.__('Register a new account').'</a></p>';
		
		echo $res;
	}
}
?>