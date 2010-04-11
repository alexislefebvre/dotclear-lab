<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of filesAlias, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

class FilesAliases
{
	protected $core;
	protected $aliases;
	
	public function __construct($core)
	{
		$this->core =& $core;
	}
	
	public function getAliases()
	{
		if (is_array($this->aliases)) {
			return $this->aliases;
		}
		
		$this->aliases = array();
		$sql =	'SELECT filesalias_url, filesalias_destination, filesalias_position, filesalias_disposable '.
				'FROM '.$this->core->prefix.'filesalias '.
				"WHERE blog_id = '".$this->core->con->escape($this->core->blog->id)."' ".
				'ORDER BY filesalias_position ASC ';
		$this->aliases = $this->core->con->select($sql)->rows();
		return $this->aliases;
	}
	
	public function getAlias($url)
	{
		$strReq = 'SELECT filesalias_url, filesalias_destination, filesalias_position, filesalias_disposable '.
				'FROM '.$this->core->prefix.'filesalias '.
				"WHERE blog_id = '".$this->core->con->escape($this->core->blog->id)."' ".
				"AND filesalias_url = '".$this->core->con->escape($url)."' ".
				'ORDER BY filesalias_position ASC ';

		$rs = $this->core->con->select($strReq);
		return $rs;
	}
	
	public function updateAliases($aliases)
	{
		$this->core->con->begin();
		try
		{
			$this->deleteAliases();
			foreach ($aliases as $k => $v)
			{
				if (!empty($v['filesalias_url']) && !empty($v['filesalias_destination']))
				{
					$this->createAlias($v['filesalias_url'],$v['filesalias_destination'],$k+1,$v['filesalias_disposable']);
				}
			}
			
			$this->core->con->commit();
		}
		catch (Exception $e)
		{
			$this->core->con->rollback();
			throw $e;
		}
	}
	
	public function createAlias($url,$destination,$position,$disposable=0)
	{
		if (!$url) {
			throw new Exception(__('File URL is empty.'));
		}
		
		if (!$destination) {
			throw new Exception(__('File destination is empty.'));
		}
		
		$cur = $this->core->con->openCursor($this->core->prefix.'filesalias');
		$cur->blog_id = (string) $this->core->blog->id;
		$cur->filesalias_url = (string) $url;
		$cur->filesalias_destination = (string) $destination;
		$cur->filesalias_position = abs((integer) $position);
		$cur->filesalias_disposable = abs((integer) $disposable);
		$cur->insert();
	}
	
	public function deleteAliases()
	{
		$this->core->con->execute(
			'DELETE FROM '.$this->core->prefix.'filesalias '.
			"WHERE blog_id = '".$this->core->con->escape($this->core->blog->id)."' "
		);
	}

	public function deleteAlias($url)
	{
		$this->core->con->execute(
			'DELETE FROM '.$this->core->prefix.'filesalias '.
			"WHERE blog_id = '".$this->core->con->escape($this->core->blog->id)."' ".
			"AND filesalias_url = '".$this->core->con->escape($url)."' "
		);
	}
}

class  aliasMedia extends dcMedia
{
	public function __construct($core)
	{
		parent::__construct($core);
	}

	public function getMediaId($target)
	{
		$strReq =
		'SELECT media_id '.
		'FROM '.$this->table.' '.
		"WHERE media_path = '".$this->path."' ".
		"AND media_file = '".$this->con->escape($target)."' ";
		
		$rs = $this->core->con->select($strReq);
		return $rs->media_id;
	}
}
?>
