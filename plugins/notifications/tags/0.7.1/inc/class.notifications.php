<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of notifications, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class notifications
{
	protected $nid;
	protected $uid;
	protected $bid;
	protected $ntype;
	protected $ndt;
	protected $nip;
	protected $nmsg;

	public function __construct($core)
	{
		$offset 		= dt::getTimeOffset($core->blog->settings->blog_timezone);

		$this->core	=& $core;
		$this->nip	= http::realIP();
		$this->bid	= $core->blog->id;
		$this->uid	= $core->auth->userID() !== null ? $core->auth->userID() : 'unknown';
		$this->ndt	= date('Y-m-d H:i:s',time() + $offset);
		$this->config	= unserialize($core->blog->settings->notifications_config);
		$this->ntype	= array('new' => '','upd' => '','del' => '','msg' => '','err' => '','spm' => '');
	}

	public function add($type,$msg,$uid = '')
	{
		if (!array_key_exists($type,$this->ntype)) {
			return;
		}

		$this->msg = serialize(array($type,$msg));

		$this->uid = !empty($uid) ? $uid : $this->uid;

		$strReq = 'SELECT MAX(notification_id) FROM '.$this->core->prefix.'notification';

		$this->nid = $this->core->con->select($strReq)->f(0) + 1;

		$cur = $this->core->con->openCursor($this->core->prefix.'notification');
		$cur->notification_id = $this->nid;
		$cur->user_id =  $this->uid;
		$cur->blog_id = $this->bid;
		$cur->notification_type = $type;
		$cur->notification_msg = $msg;
		$cur->notification_dt = $this->ndt;
		$cur->notification_ip = $this->nip;
		$cur->insert();
	}

	public function getConfig()
	{
		return $this->config;
	}
}

?>