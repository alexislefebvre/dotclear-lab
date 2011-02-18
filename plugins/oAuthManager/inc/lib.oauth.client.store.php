<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of oAuthManager, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

class oAuthClientStore
{
	private $core;
	private $con;
	private $prefix;
	private $blog;
	private $table;
	
	public function __construct($core)
	{
		$this->core = $core;
		$this->con = $this->core->con;
		$this->prefix = $this->core->prefix;
		$this->blog = $this->con->escape($this->core->blog->id);
		$this->table = $this->con->escape($this->prefix.'oauthclient');
	}
	
	public function open()
	{
		return $this->con->openCursor($this->table);
	}
	
	public function lock()
	{
		$this->con->writeLock($this->table);
	}
	
	public function unlock()
	{
		$this->con->unlock();
	}
	
	protected function nextId()
	{
		return $this->con->select('SELECT MAX(uid) FROM '.$this->table)->f(0) + 1;
	}
	
	public function get($plugin_id,$client_id,$user_id)
	{
		$rs = $this->con->select(
			'SELECT uid, plugin_id, client_id, user_id, '.
			'name, state, token, secret, mtime, expiry, more '.
			'FROM '.$this->table.' '.
			"WHERE blog_id = '".$this->blog."' ".
			"AND plugin_id = '".$this->con->escape($plugin_id)."' ".
			"AND client_id = '".$this->con->escape($client_id)."' ".
			(null === $user_id ?
				'AND user_id IS NULL ' : 
				"AND user_id = '".$this->con->escape($user_id)."' "
			).
			$this->con->limit(1)
		);
		if ($rs->isEmpty())
		{
			return false;
		}
		if ($rs->expiry)
		{
			# remove token if client expired
			if (time() > strtotime($rs->expiry))
			{
				$this->del($rs->uid);
				return false;
			}
		}
		return $rs;
	}
	
	public function add($plugin_id,$client_id,$user_id)
	{
		$cur = $this->open();
		$this->lock();
		
		try {
			$cur->uid = $this->nextId();
			$cur->blog_id = $this->blog;
			$cur->plugin_id = $this->con->escape($plugin_id);
			$cur->client_id = $this->con->escape($client_id);
			$cur->user_id = $user_id === null ? null : $this->con->escape($user_id);
			$cur->mtime = date('Y-m-d H:i:s');
			
			$cur->insert();
			$this->unlock();
			
			$res = new arrayObject();
			$res['uid'] = $cur->uid;
			$res['plugin_id'] = $plugin_id;
			$res['client_id'] = $client_id;
			$res['user_id'] = $user_id;
			$res['blog_id'] = $this->blog;
			$res['name'] = null;
			$res['token'] = null;
			$res['secret'] = null;
			$res['more'] = null;
			$res['mtime'] = $cur->mtime;
			$res['expiry'] = null;
		}
		catch (Exception $e)
		{
			$this->unlock();
			throw $e;
		}
		return false;
	}
	
	public function upd($uid,$cur,$expiry=null)
	{
		$uid = (integer) $uid;
		$expiry = (integer) $expiry;
		
		if (empty($uid))
		{
			throw new Exception(__('No such store ID'));
		}
		$time = time();

		$cur->mtime = date('Y-m-d H:i:s',$time);
		if (false !== $expiry) {
			$cur->expiry = $expiry ? date('Y-m-d H:i:s',$time + $expiry) : null;
		}
		$cur->update("WHERE uid = ".$uid." AND blog_id = '".$this->blog."' ");
	}
	
	public function del($uid)
	{
		$uid = (integer) $uid;
		$this->con->execute('DELETE FROM '.$this->table.' WHERE uid = '.$uid);
	}
}