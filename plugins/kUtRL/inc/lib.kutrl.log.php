<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of kUtRL, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

# This file contents class to acces local short links records

class kutrlLog
{
	public $core;
	public $table;
	public $blog;
	public $con;

	public function __construct($core)
	{
		$this->core = $core;
		$this->table = $core->prefix.'kutrl';
		$this->blog = $core->con->escape($core->blog->id);
		$this->con = $core->con;
	}

	public function nextId()
	{
		return $this->con->select(
			'SELECT MAX(kut_id) FROM '.$this->table
		)->f(0) + 1;
	}

	public function insert($url,$hash,$type,$service='kutrl')
	{
		$cur = $this->con->openCursor($this->table);
		$this->con->writeLock($this->table);

		try {
			$cur->kut_id = $this->nextId();
			$cur->blog_id = $this->blog;
			$cur->kut_url = (string) $url;
			$cur->kut_hash = (string) $hash;
			$cur->kut_type = (string) $type;
			$cur->kut_service = (string) $service;
			$cur->kut_dt = date('Y-m-d H:i:s');
			$cur->kut_counter = 0;

			$cur->insert();
			$this->con->unlock();

			return array(
				'id' => $cur->kut_id,
				'url' => $url,
				'hash' => $hash,
				'type' => $type,
				'service' => $service,
				'counter '=> 0
			);
		}
		catch (Exception $e)
		{
			$this->con->unlock();
			throw $e;
		}
		return false;
	}

	public function select($url=null,$hash=null,$type=null,$service='kutrl')
	{
		//$this->con->writeLock($this->table);

		$req = 
		'SELECT kut_id as id, kut_hash as hash, kut_url as url, '.
		'kut_type as type, kut_service as service, kut_counter as counter '.
		'FROM '.$this->table.' '.
		"WHERE blog_id = '".$this->blog."' ".
		"AND kut_service = '".$this->con->escape($service)."' ";

		if (null !== $url)
			$req .= "AND kut_url = '".$this->con->escape($url)."' ";

		if (null !== $hash)
			$req .= "AND kut_hash = '".$this->con->escape($hash)."' ";

		if (null !== $type) {
			if (is_array($type)) {
				$req .= "AND kut_type '".$this->con->in($type)."' ";
			}
			else {
				$req .= "AND kut_type = '".$this->con->escape($type)."' ";
			}
		}

		$req .= 'ORDER BY kut_dt DESC '.$this->con->limit(1);

		$rs = $this->con->select($req);
		//$this->con->unlock();

		return $rs->isEmpty() ? false : $rs;
	}
	
	public function clear($id)
	{
		$id = (integer) $id;
		
		$cur = $this->con->openCursor($this->table);
		$this->con->writeLock($this->table);
		
		try
		{
			$cur->kut_url = '';
			$cur->kut_dt = date('Y-m-d H:i:s');
			$cur->kut_counter = 0;
			
			$cur->update(
				"WHERE blog_id='".$this->blog."' ".
				"AND kut_id='".$id."' "
			);
			$this->con->unlock();
			
			return true;
		}
		catch (Exception $e)
		{
			$this->con->unlock();
			throw $e;
		}
		return false;
	}

	public function delete($id)
	{
		$id = (integer) $id;

		return $this->con->execute(
			'DELETE FROM '.$this->table.' '.
			"WHERE blog_id='".$this->blog."' ".
			"AND kut_id='".$id."' "
		);
	}

	public function counter($id,$do='get')
	{
		$id = (integer) $id;

		$rs = $this->con->select(
			'SELECT kut_counter '.
			'FROM '.$this->table.' '.
			"WHERE blog_id='".$this->blog."' ".
			"AND kut_id='".$id."' "
		);

		$counter = $rs->isEmpty() ? 0 : $rs->kut_counter;

		if ('get' == $do)
		{
			return $counter;
		}
		elseif ('up' == $do)
		{
			$counter += 1;
		}
		elseif ('reset' == $do)
		{	
			$counter = 0;
		}
		else
		{
			return 0;
		}

		$cur = $this->con->openCursor($this->table);
		$this->con->writeLock($this->table);

		$cur->kut_counter = (integer) $counter;
		$cur->update(
			"WHERE blog_id='".$this->blog."' ".
			"AND kut_id='".$id."'"
		);
		$this->con->unlock();

		return $counter;
	}

	public function getLogs($p,$count_only=false)
	{
		if ($count_only)
		{
			$r = 'SELECT count(S.kut_id) ';
		}
		else
		{
			$content_req = '';
			
			if (!empty($p['columns']) && is_array($p['columns']))
			{
				$content_req .= implode(', ',$p['columns']).', ';
			}
			$r = 
			'SELECT S.kut_id, S.kut_type, S.kut_hash, S.kut_url, '.
			$content_req.'S.kut_dt ';
		}

		$r .= 'FROM '.$this->table.' S ';

		if (!empty($p['from']))
		{
			$r .= $p['from'].' ';
		}
		$r .= "WHERE S.blog_id = '".$this->blog."' ";

		if (isset($p['kut_service']))
		{
			$r .= "AND kut_service='".$this->con->escape($p['kut_service'])."' ";
		}
		else
		{
			$r .= "AND kut_service='kutrl' ";
		}

		if (isset($p['kut_type']))
		{
			if (is_array($p['kut_type']) && !empty($p['kut_type']))
			{
				$r .= 'AND kut_type '.$this->con->in($p['kut_type']);
			}
			elseif ($p['kut_type'] != '')
			{
				$r .= "AND kut_type = '".$this->con->escape($p['kut_type'])."' ";
			}
		}

		if (isset($p['kut_id']))
		{
			if (is_array($p['kut_id']) && !empty($p['kut_id']))
			{
				$r .= 'AND kut_id '.$this->con->in($p['kut_id']);
			}
			elseif ($p['kut_id'] != '')
			{
				$r .= "AND kut_id = '".$this->con->escape($p['kut_id'])."' ";
			}
		}

		if (isset($p['kut_hash']))
		{
			if (is_array($p['kut_hash']) && !empty($p['kut_hash']))
			{
				$r .= 'AND kut_hash '.$this->con->in($p['kut_hash']);
			}
			elseif ($p['kut_hash'] != '')
			{
				$r .= "AND kut_hash = '".$this->con->escape($p['kut_hash'])."' ";
			}
		}

		if (isset($p['kut_url']))
		{
			if (is_array($p['kut_url']) && !empty($p['kut_url']))
			{
				$r .= 'AND kut_url '.$this->con->in($p['kut_url']);
			}
			elseif ($p['kut_url'] != '')
			{
				$r .= "AND kut_url = '".$this->con->escape($p['kut_url'])."' ";
			}
		}

		if (!empty($p['kut_year']))
		{
			$r .= 
			'AND '.$this->con->dateFormat('kut_dt','%Y').' = '.
			"'".sprintf('%04d',$p['kut_year'])."' ";
		}

		if (!empty($p['kut_month']))
		{
			$r .= 
			'AND '.$this->con->dateFormat('kut_dt','%m').' = '.
			"'".sprintf('%02d',$p['kut_month'])."' ";
		}

		if (!empty($p['kut_day']))
		{
			$r .= 
			'AND '.$this->con->dateFormat('kut_dt','%d').' = '.
			"'".sprintf('%02d',$p['kut_day'])."' ";
		}

		if (!empty($p['sql']))
		{
			$r .= $p['sql'].' ';
		}

		if (!$count_only)
		{
			$r .= empty($p['order']) ?
				'ORDER BY kut_dt DESC ' :
				'ORDER BY '.$this->con->escape($p['order']).' ';
		}

		if (!$count_only && !empty($p['limit']))
		{
			$r .= $this->con->limit($p['limit']);
		}

		return $this->con->select($r);
	}
}
?>