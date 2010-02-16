<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of fac, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------


class fac
{
	public $core;
	public $con;
	protected $table;
	protected $type;
	
	public function __construct($core,$type='fac')
	{
		$this->core =& $core;
		$this->con = $core->con;
		$this->table = $core->prefix.'meta';
		$this->type = $core->con->escape($type);
	}
	
	public function getFac($post_id=null,$url=null,$limit=null)
	{
		$q = 'SELECT meta_id, meta_type, COUNT(M.post_id) as count '.
		'FROM '.$this->table.' M LEFT JOIN '.$this->core->prefix.'post P '.
		'ON M.post_id = P.post_id '.
		"WHERE P.blog_id = '".$this->con->escape($this->core->blog->id)."' ".
		"AND meta_type = '".$this->type."' ";

		if ($post_id !== null) {
			$q .= ' AND P.post_id = '.(integer) $post_id.' ';
		}
		if ($url !== null) {
			$q .= " AND meta_id = '".$this->con->escape($url)."' ";
		}

		if (!$this->core->auth->check('contentadmin',$this->core->blog->id)) {
			$q .= 'AND ((post_status = 1 ';
			
			if ($this->core->blog->without_password) {
				$q .= 'AND post_password IS NULL ';
			}
			$q .= ') ';
			
			if ($this->core->auth->userID()) {
				$q .= "OR P.user_id = '".$this->con->escape($this->core->auth->userID())."')";
			} else {
				$q .= ') ';
			}
		}

		$q .=
		'GROUP BY meta_id,meta_type,P.blog_id '.
		'ORDER BY count DESC';

		if ($limit) {
			$q .= $this->con->limit($limit);
		}

		return $this->con->select($q);
	}
	
	public function delFac($post_id=null,$url=null)
	{
		$q = "DELETE FROM ".$this->table." WHERE meta_type = '".$this->type."' ";

		if ($post_id !== null) {
			$q .= " AND post_id = '".$this->con->escape($post_id)."' ";
		}
		if ($url !== null) {
			$q .= " AND meta_id = '".$this->con->escape($url)."' ";
		}

		$this->con->execute($q);
	}
	
	public function setFac($post_id,$url)
	{
		if (!self::validateURL($url)) return;

		$this->delFac($post_id);

		$cur = $this->con->openCursor($this->table);
		$cur->post_id = (integer) $post_id;
		$cur->meta_id = (string) $url;
		$cur->meta_type = $this->type;
		$cur->insert();
	}

	public static function validateURL($str)
	{
		$str = trim($str);
		if (empty($str) 
		 || (function_exists('filter_var') && !filter_var($str, FILTER_VALIDATE_URL)) 
		 || false === strpos($str,'http://') 
		 || false !== strpos($str,'http://192.') 
		 || false !== strpos($str,'localhost')) 
		{
			return false;
		}
		return true;
	}
}