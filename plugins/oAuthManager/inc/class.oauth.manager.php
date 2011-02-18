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

if (!defined('DC_CONTEXT_ADMIN')){return;}

class oAuthManager
{
	private $core;
	private $con;
	private $prefix;
	private $blog;
	
	private $client_table;
	
	public function __construct($core)
	{
		$this->core = $core;
		$this->con = $this->core->con;
		$this->prefix = $this->core->prefix;
		$this->blog = $this->con->escape($this->core->blog->id);
	
		$this->client_table = $this->con->escape($this->prefix.'oauthclient');
	}
	
	private function blog()
	{
		return $this->core->auth->isSuperAdmin() ? 
			'' : " AND blog_id = '".$this->blog."' ";
	}
	
	public function getClients($params=array(),$count_only=false)
	{
		if ($count_only)
		{
			$strReq = 'SELECT count(C.uid) ';
		}
		else
		{
			$strReq = 'SELECT * ';
		}
		
		$strReq .= 'FROM '.$this->client_table.' C ';
		
		if (!empty($params['from']))
		{
			$strReq .= $params['from'].' ';
		}
		
		$strReq .= "WHERE C.uid > 0 "; //always true huhu
		
		$strReq .= $this->blog();
		
		if (!empty($params['sql']))
		{
			$strReq .= $params['sql'].' ';
		}
		
		if (!$count_only)
		{
			if (!empty($params['order']))
			{
				$strReq .= 'ORDER BY '.$this->con->escape($params['order']).' ';
			}
			else
			{
				$strReq .= 'ORDER BY C.mtime DESC ';
			}
		}
		
		if (!$count_only && !empty($params['limit']))
		{
			$strReq .= $this->con->limit($params['limit']);
		}
		
		$rs = $this->con->select($strReq);
		$rs->core = $this->core;
		
		return $rs;
	}
	
	public function deleteClient($uid)
	{
		$uid = (integer) $uid;
		$this->con->execute(
			'DELETE FROM '.$this->client_table.' '.
			'WHERE uid = '.$uid.' '.$this->blog()
		);
	}
	
	public function resetClient($uid)
	{
		$uid = (integer) $uid;
		
		if (empty($uid))
		{
			throw new Exception(__('No such store ID'));
		}
		
		$cur = $this->con->openCursor($this->client_table);
		
		$cur->token = null;
		$cur->secret = null;
		$cur->name = null;
		$cur->state = 0;
		$cur->mtime = date('Y-m-d H:i:s');
		
		$cur->update('WHERE uid = '.$uid.' '.$this->blog());
	}
	
	public function deleteDisconnected()
	{
		$this->con->execute(
			'DELETE FROM '.$this->client_table.' '.
			'WHERE state = 0 '.$this->blog()
		);
	}
	
	public function deleteExpired()
	{
		$this->con->execute(
			'DELETE FROM '.$this->client_table.' '.
			'WHERE expiry IS NOT NULL AND mtime < expiry '.
			$this->blog()
		);
	}
}

?>