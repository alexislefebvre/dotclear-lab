<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of soCialMe, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------


class soCialMeCache
{
	private $core;
	private $con;
	private $table;
	private $blog;
	private $type;
	
	public function __construct($core,$type)
	{
		$this->core = $core;
		$this->con = $this->core->con;
		$this->table = $this->core->prefix.'socialcache';
		$this->blog = $this->con->escape($this->core->blog->id);
		$this->type = $this->con->escape($type);
	}
	
	private function open()
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
	
	private function nextId()
	{
		return $this->con->select('SELECT MAX(cache_id) FROM '.$this->table)->f(0) + 1;
	}
	
	public function get($method)
	{
		$rs = $this->con->select(
			'SELECT * FROM '.$this->table.' '.
			"WHERE blog_id = '".$this->blog."' ".
			"AND cache_type = '".$this->type."' ".
			"AND cache_method = '".$this->con->escape($method)."' ".
			$this->con->limit(1)
		);
		if ($rs->isEmpty()) return array();
		
		return array(
			'id' => $rs->cache_id,
			'date' => $rs->cache_dt,
			'method' => $rs->cache_method,
			'content' => base64_decode($rs->cache_content)
		);
	}
	
	public function add($method,$content)
	{
		if (empty($method) || empty($content)) {
			throw new Exception('Nothing to add');
		}
		
		$cur = $this->open();
		$this->lock();
		
		try {
			$cur->cache_id = $this->nextId();
			$cur->blog_id = $this->blog;
			$cur->cache_dt = date('Y-m-d H:i:s');
			$cur->cache_type = $this->type;
			$cur->cache_method = $method;
			$cur->cache_content = base64_encode($content);
			
			$cur->insert();
			$this->unlock();
			
			return $cur->cache_id;
		}
		catch (Exception $e)
		{
			$this->unlock();
			throw $e;
		}
		return false;
	}
	
	public function del($method)
	{
		$method = (string) $method;
		
		$this->con->execute(
			'DELETE FROM '.$this->table.' '.
			"WHERE blog_id = '".$this->blog."' ".
			"AND cache_type = '".$this->type."' ".
			"AND cache_method = '".$this->con->escape($method)."' "
		);
	}
	
	public function clean()
	{
		$this->con->execute(
			'DELETE FROM '.$this->table.' '.
			"WHERE blog_id = '".$this->blog."' ".
			"AND cache_type = '".$this->type."' "
		);
	}
}

?>