<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of dctribune, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 Osku  and contributors
# Many thanks to Pep, Tomtom and JcDenis
# Originally from Antoine Libert
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

class dcTribune
{
	public function __construct(&$core)
	{
		$this->core =& $core;
		$this->con = $this->core->con;
		$this->table = $this->core->prefix.'tribune';
	}
	
	//public function addMsg($nick, $message, $time, $ip, $state=1)
	public function addMsg($cur)
	{
		//$cur = $this->con->openCursor($this->table);
		$this->con->writeLock($this->table);
		try
		{
			# Get ID
			$rs = $this->con->select(
				'SELECT MAX(tribune_id) '.
				'FROM '.$this->table
			);
			
			$cur->tribune_id = (integer) $rs->f(0) + 1;
			$cur->blog_id = (string) $this->core->blog->id;		
			
			$offset = dt::getTimeOffset($this->core->blog->settings->blog_timezone);
			$cur->tribune_dt = date('Y-m-d H:i:s',time()+$offset);
			
			//$this->getMsgContent($cur,$cur->post_id);
			
			$this->getMsgCursor($cur);
			
			if ($cur->tribune_ip === null) {
				$cur->tribune_ip = http::realIP();
			}
			
			$cur->insert();
			$this->con->unlock();
			//self::$blog->triggerBlog();
		}
		catch (Exception $e)
		{
			$this->con->unlock();
			throw $e;
		}

		return $cur->tribune_id;
	}

	public function delMsg($id)
	{
		$strReq = 'DELETE FROM '.$this->table.' '.
				"WHERE blog_id = '".$this->con->escape($this->core->blog->id)."' ".
				'AND tribune_id = '.(integer) $id.' ';
		
		$this->con->execute($strReq);
	}

	public function updateMsg($id, &$cur)
	{
		$id = (integer) $id;
		
		if (empty($id)) {
			throw new Exception(__('No such message ID'));
		}
		
		$this->getMsgCursor($cur);
		
		$cur->update('WHERE tribune_id = '.(integer) $id .
			" AND blog_id = '".$this->con->escape($this->core->blog->id)."'");
		$this->core->blog->triggerBlog();
	}
  
	public function changeState($id, $state, $check=false, $time, $deltime, $ip)
	{
		if ($check) {
			$strReq = 'SELECT tribune_id FROM '.$this->table." WHERE tribune_dt > '".(string) date('Y-m-d H:i',$time - $deltime)."' AND tribune_ip = '".(string) $this->con->escape($ip)."' AND tribune_id = '".(integer) $_GET['tribdel']."' ORDER BY tribune_id DESC";
			$strReq .= $this->con->limit(1);
			$rs = $this->con->select($strReq);
	
			
			if (empty($rs))
				return false;
		}
	
		$cur = $this->con->openCursor($this->table);
		
		$cur->tribune_state = (string) $state;
			
		$cur->update('WHERE tribune_id = '.(integer) $id.
			" AND blog_id = '".$this->con->escape($this->core->blog->id)."'");
		$this->core->blog->triggerBlog();
		return true;
	}
	
	public function getMsgs($params=array(),$count_only=false)
	{
		if ($count_only)
		{
			$strReq = 'SELECT count(tribune_id) '.
				'FROM '.$this->table.' '.
				"WHERE blog_id = '".$this->con->escape($this->core->blog->id)."'";
		}
		else
		{
			$strReq = 
				'SELECT tribune_id, tribune_nick, tribune_ip, '.
				'tribune_dt, tribune_msg, tribune_state '.
				'FROM '.$this->table.' '.
				"WHERE blog_id = '".$this->con->escape($this->core->blog->id)."'";
			
			if (isset($params['tribune_state'])) {
				$strReq .= ' AND tribune_state = '. (integer) $params['tribune_state'].'  ';
			}
			
			if (!empty($params['tribune_state_not']))
			{
				$strReq .= 'AND tribune_state <> '.(integer) $params['tribune_state_not'].' ';
			}
			
			if (!empty($params['tribune_ip']))
			{
				$strReq .= "AND tribune_ip = '".$this->con->escape($params['tribune_ip'])."' ";
			}	
			
			if (!empty($params['order'])) {
				$strReq .= 'ORDER BY '.$this->con->escape($params['order']).' ';
			}
			else {
				$strReq .= ' ORDER BY tribune_dt DESC';
			}
			
			if (!empty($params['limit'])) {
				$strReq .= $this->con->limit($params['limit']);
			}
		}
		//Behavior ?
		$rs = $this->con->select($strReq);
		return $rs;
	}
	
	public function getOneMsg($id)
	{
		# On récupère une seule ligne
		$strReq = 'SELECT tribune_id, tribune_nick, tribune_msg FROM '.$this->table." WHERE tribune_id = '".(integer) $id."'";
		
		$rs = $this->con->select($strReq);

		
		return $rs;
	}
	
	public function cleanMsg($msg)
	{
		$msg = ereg_replace("[ ]{2,}"," ",$msg);
		
		# url2link
		$msg = eregi_replace(
			"([[:alnum:]]+)://([^[:space:]]*)([[:alnum:]#?/&=])",
			"<a href=\"\\1://\\2\\3\" title=\"\\1://\\2\\3\" rel=\"nofollow\">[url]</a>",$msg);
		
		# mail2link
		$msg = eregi_replace( "(([a-z0-9_]|\\-|\\.)+@([^[:space:]]*)([[:alnum:]-]))",
			"<a href=\"mailto:\\1\" title=\"\\1\">[mail]</a>",$msg);
		
		return $msg;
	}
	
	private function getMsgCursor(&$cur)
	{
		if ($cur->tribune_nick !== null && $cur->tribune_nick == '') {
			throw new Exception(__('You must provide a nick'));
		}
		
		if ($cur->tribune_msg !== null && $cur->tribune_msg == '') {
			throw new Exception(__('You must provide a message'));
		}
	}
}
?>