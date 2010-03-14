<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear Galaxy plugin.
#
# Dotclear Galaxy plugin is free software: you can redistribute it
# and/or modify  it under the terms of the GNU General Public License
# version 2 of the License as published by the Free Software Foundation.
#
# Dotclear Galaxy plugin is distributed in the hope that it will be
# useful, but WITHOUT ANY WARRANTY; without even the implied warranty
# of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Dotclear Galaxy plugin.
# If not, see <http://www.gnu.org/licenses/>.
#
# Copyright (c) 2010 Mounir Lamouri.
# Based on the Dotclear metadata plugin by Olivier Meunier.
#
# -- END LICENSE BLOCK ------------------------------------

class dcGalaxy
{
	private $core;
	private $con;
	private $table;
	
	public function __construct(&$core)
	{
		$this->core =& $core;
		$this->con =& $this->core->con;
		$this->table = $this->core->prefix.'galaxy';
	}
	
	public static function sanitizePlanetID($str)
	{
		return text::tidyURL($str,false,true);
	}
	
	private function checkPermissionsOnPost($post_id)
	{
		$post_id = (integer) $post_id;
		
		if (!$this->core->auth->check('usage,contentadmin',$this->core->blog->id)) {
			throw new Exception(__('You are not allowed to change this entry status'));
		}
		
		# If user can only publish, we need to check the post's owner
		if (!$this->core->auth->check('contentadmin',$this->core->blog->id))
		{
			$strReq = 'SELECT post_id '.
					'FROM '.$this->core->prefix.'post '.
					'WHERE post_id = '.$post_id.' '.
					"AND user_id = '".$this->con->escape($this->core->auth->userID())."' ";
			
			$rs = $this->con->select($strReq);
			
			if ($rs->isEmpty()) {
				throw new Exception(__('You are not allowed to change this entry status'));
			}
		}
	}
	
	public function getPostsByPlanet($params=array(),$count_only=false)
	{
		if (!isset($params['planet_id'])) {
			return null;
		}
		
		$params['from'] = ', '.$this->table.' META ';
		$params['sql'] = 'AND META.post_id = P.post_id ';
		
		$params['sql'] .= "AND META.planet_id = '".$this->con->escape($params['planet_id'])."' ";
		
		unset($params['planet_id']);
		
		return $this->core->blog->getPosts($params,$count_only);
	}
	
	public function getPlanets($limit=null,$planet_id=null,$post_id=null)
	{
		$strReq = 'SELECT planet_id, COUNT(M.post_id) as count '.
		'FROM '.$this->table.' M LEFT JOIN '.$this->core->prefix.'post P '.
		'ON M.post_id = P.post_id '.
		"WHERE P.blog_id = '".$this->con->escape($this->core->blog->id)."' ";
		
		if ($planet_id !== null) {
			$strReq .= " AND planet_id = '".$this->con->escape($planet_id)."' ";
		}
		
		if ($post_id !== null) {
			$strReq .= ' AND P.post_id = '.(integer) $post_id.' ';
		}
		
		if (!$this->core->auth->check('contentadmin',$this->core->blog->id)) {
			$strReq .= 'AND ((post_status = 1 ';
			
			if ($this->core->blog->without_password) {
				$strReq .= 'AND post_password IS NULL ';
			}
			$strReq .= ') ';
			
			if ($this->core->auth->userID()) {
				$strReq .= "OR P.user_id = '".$this->con->escape($this->core->auth->userID())."')";
			} else {
				$strReq .= ') ';
			}
		}
		
		$strReq .=
		'GROUP BY planet_id,P.blog_id '.
		'ORDER BY count DESC';
		
		if ($limit) {
			$strReq .= $this->con->limit($limit);
		}

		$rs = $this->con->select($strReq);
		$rs = $rs->toStatic();

		return $rs;
	}
	
	public function setPostPlanet($post_id,$value)
	{
		$this->checkPermissionsOnPost($post_id);
		
		$value = trim($value);
		if (!$value) { return; }
		
		$cur = $this->con->openCursor($this->table);
		
		$cur->post_id = (integer) $post_id;
		$cur->planet_id = (string) $value;
		
		$cur->insert();
	}
	
	public function delPostPlanet($post_id,$planet_id=null)
	{
		$post_id = (integer) $post_id;
		
		$this->checkPermissionsOnPost($post_id);
		
		$strReq = 'DELETE FROM '.$this->table.' '.
				'WHERE post_id = '.$post_id;
		
		if ($planet_id !== null) {
			$strReq .= " AND planet_id = '".$this->con->escape($planet_id)."' ";
		}
		
		$this->con->execute($strReq);
	}
	
	public function updatePlanet($planet_id,$new_planet_id)
	{
		$new_planet_id = self::sanitizePlanetID($new_planet_id);
		
		if ($new_planet_id == $planet_id) {
			return true;
		}
		
		$getReq = 'SELECT M.post_id '.
				'FROM '.$this->table.' M, '.$this->core->prefix.'post P '.
				'WHERE P.post_id = M.post_id '.
				"AND P.blog_id = '".$this->con->escape($this->core->blog->id)."' ".
				"AND planet_id = '%s' ";
		
		if (!$this->core->auth->check('contentadmin',$this->core->blog->id)) {
			$getReq .= "AND P.user_id = '".$this->con->escape($this->core->auth->userID())."' ";
		}
		
		$delReq = 'DELETE FROM '.$this->table.' '.
				'WHERE post_id IN (%s) '.
				"AND planet_id = '%s' ";
		
		$updReq = 'UPDATE '.$this->table.' '.
				"SET planet_id = '%s' ".
				'WHERE post_id IN (%s) '.
				"AND planet_id = '%s' ";
		
		$to_update = $to_remove = array();
		
		$rs = $this->con->select(sprintf($getReq,$this->con->escape($planet_id)));
		
		while ($rs->fetch()) {
			$to_update[] = $rs->post_id;
		}
		
		if (empty($to_update)) {
			return false;
		}
		
		$rs = $this->con->select(sprintf($getReq,$new_planet_id));
		while ($rs->fetch()) {
			if (in_array($rs->post_id,$to_update)) {
				$to_remove[] = $rs->post_id;
				unset($to_update[array_search($rs->post_id,$to_update)]);
			}
		}
		
		# Delete duplicate planets
		if (!empty($to_remove))
		{
			$this->con->execute(sprintf($delReq,implode(',',$to_remove),
							$this->con->escape($planet_id)));
		}
		
		# Update planets
		if (!empty($to_update))
		{
			$this->con->execute(sprintf($updReq,$this->con->escape($new_planet_id),
							implode(',',$to_update),
							$this->con->escape($planet_id)));
		}
		
		return true;
	}
	
	public function delPlanet($planet_id)
	{
		$strReq = 'SELECT M.post_id '.
				'FROM '.$this->table.' M, '.$this->core->prefix.'post P '.
				'WHERE P.post_id = M.post_id '.
				"AND P.blog_id = '".$this->con->escape($this->core->blog->id)."' ".
				"AND planet_id = '".$this->con->escape($planet_id)."' ";
		
		$rs = $this->con->select($strReq);
		
		$ids = array();
		while ($rs->fetch()) {
			$ids[] = $rs->post_id;
		}
		
		$strReq = 'DELETE FROM '.$this->table.' '.
				'WHERE post_id IN ('.implode(',',$ids).') '.
				"AND planet_id = '".$this->con->escape($planet_id)."' ";
		
		$rs = $this->con->execute($strReq);
		
		return $ids;
	}
}
?>
