<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of agora, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 Osku , Tomtom and contributors
## Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

class dcPublicAuth extends dcAuth
{
	public function __construct($core)
	{
		parent::__construct($core);
	}

	public function checkPublicUser($user_id, $pwd=null, $user_key=null)
	{
		# Check user and password
		$strReq = 'SELECT user_id, user_super, user_pwd, user_name, '.
				'user_firstname, user_displayname, user_email, user_url, '.
				'user_default_blog, user_options, '.
				'user_lang, user_tz, user_post_status, user_creadt, user_upddt '.
				'FROM '.$this->con->escapeSystem($this->user_table).' '.
				"WHERE user_id = '".$this->con->escape($user_id)."' ";
		
		$rs = $this->con->select($strReq);
		
		if ($rs->isEmpty()) {
			return false;
		}
		
		$rs->extend('rsExtUser');
		
		if ($pwd != '')
		{
			if (crypt::hmac(DC_MASTER_KEY,$pwd) != $rs->user_pwd) {
				sleep(rand(2,5));
				return false;
			}
		}
		elseif ($user_key != '')
		{
			if (http::browserUID(DC_MASTER_KEY.$rs->user_id.$rs->user_pwd) != $user_key) {
				return false;
			}
		}
		
		$this->user_id = $rs->user_id;
		$this->user_admin = (boolean) $rs->user_super;
		
		$this->user_info['user_pwd'] = $rs->user_pwd;
		$this->user_info['user_name'] = $rs->user_name;
		$this->user_info['user_firstname'] = $rs->user_firstname;
		$this->user_info['user_displayname'] = $rs->user_displayname;
		$this->user_info['user_email'] = $rs->user_email;
		$this->user_info['user_url'] = $rs->user_url;
		$this->user_info['user_default_blog'] = $rs->user_default_blog;
		$this->user_info['user_lang'] = $rs->user_lang;
		$this->user_info['user_tz'] = $rs->user_tz;
		$this->user_info['user_post_status'] = $rs->user_post_status;
		$this->user_info['user_creadt'] = $rs->user_creadt;
		$this->user_info['user_upddt'] = $rs->user_upddt;
		
		$this->user_info['user_cn'] = dcUtils::getUserCN($rs->user_id, $rs->user_name,
		$rs->user_firstname, $rs->user_displayname);
		
		$this->user_options = array_merge($this->core->userDefaults(),$rs->options());
		
		# Get permissions on blogs : needed for admin not for public
		//if (self::findUserBlog() === false) {
		//	return false;
		//}
		
		return true;
	}
}
?>