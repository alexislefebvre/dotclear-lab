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
if (defined('DC_AUTH_CLASS')) {
	class_alias('DC_AUTH_CLASS','agoraAuthParent');
} else {
	class_alias('dcAuth','agoraAuthParent');
}

class dcPublicAuth extends agoraAuthParent
{
	public function __construct($core)
	{
		parent::__construct($core);
	}

	public function checkPublicUser($user_id, $pwd=null, $user_key=null)
	{
		parent::checkUser($user_id,$pwd,$user_key);
		if (isset($this->user_info['user_creadt'])) {
			return true;
		} else {
			return false;
		}
	}
}
?>