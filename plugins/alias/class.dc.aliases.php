<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2.
#
# Copyright (c) 2003-2008 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

class dcAliases
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
		$sql =	'SELECT alias_url, alias_destination, alias_position '.
				'FROM '.$this->core->prefix.'alias '.
				"WHERE blog_id = '".$this->core->con->escape($this->core->blog->id)."' ".
				'ORDER BY alias_position ASC ';
		$this->aliases = $this->core->con->select($sql)->rows();
		return $this->aliases;
	}
	
	public function updateAliases($aliases)
	{
		usort($aliases,array($this,'sortCallback'));
		foreach ($aliases as $v) {
			if (!isset($v['alias_url']) || !isset($v['alias_destination'])) {
				throw new Exception(__('Invalid aliases definitions'));
			}
		}
		
		$this->core->con->begin();
		try
		{
			$this->deleteAliases();
			foreach ($aliases as $k => $v)
			{
				if (!empty($v['alias_url']) && !empty($v['alias_destination']))
				{
					$this->createAlias($v['alias_url'],$v['alias_destination'],$k+1);
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
			throw new Exception(__('Alias URL is empty.'));
		}
		
		if (!$destination) {
			throw new Exception(__('Alias destination is empty.'));
		}
		
		$cur = $this->core->con->openCursor($this->core->prefix.'alias');
		$cur->blog_id = (string) $this->core->blog->id;
		$cur->alias_url = (string) $url;
		$cur->alias_destination = (string) $destination;
		$cur->alias_position = abs((integer) $position);
		$cur->insert();
	}
	
	public function deleteAlias($url) 
	{ 
		$this->core->con->execute( 
			'DELETE FROM '.$this->core->prefix.'alias '. 
			"WHERE blog_id = '".$this->core->con->escape($this->core->blog->id)."' ". 
			"AND alias_url = '".$this->core->con->escape($url)."' " 
		); 
	} 

	public function deleteAliases()
	{
		$this->core->con->execute(
			'DELETE FROM '.$this->core->prefix.'alias '.
			"WHERE blog_id = '".$this->core->con->escape($this->core->blog->id)."' "
		);
	}
	
	protected function sortCallback($a,$b)
	{
		if ($a['alias_position'] == $b['alias_position']) {
			return 0;
		}
		return $a['alias_position'] < $b['alias_position'] ? -1 : 1;
	}
}
?>
