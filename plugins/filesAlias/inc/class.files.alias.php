<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of filesAlias, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 Osku and contributors
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
	
	public function __construct(&$core)
	{
		$this->core =& $core;
	}
	
	public function getAliases()
	{
		if (is_array($this->aliases)) {
			return $this->aliases;
		}
		
		$this->aliases = array();
		$sql =	'SELECT filesalias_url, filesalias_destination, filesalias_position '.
				'FROM '.$this->core->prefix.'filesalias '.
				"WHERE blog_id = '".$this->core->con->escape($this->core->blog->id)."' ".
				'ORDER BY filesalias_position ASC ';
		$this->aliases = $this->core->con->select($sql)->rows();
		return $this->aliases;
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
					$this->createAlias($v['filesalias_url'],$v['filesalias_destination'],$k+1);
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
	
	public function createAlias($url,$destination,$position)
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
		$cur->insert();
	}
	
	public function deleteAliases()
	{
		$this->core->con->execute(
			'DELETE FROM '.$this->core->prefix.'filesalias '.
			"WHERE blog_id = '".$this->core->con->escape($this->core->blog->id)."' "
		);
	}
}
?>