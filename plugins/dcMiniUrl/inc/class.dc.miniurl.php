<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcMiniUrl, a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

class dcMiniUrl
{
	public $core;
	public $table;
	public $blog;
	public $con;

	protected $enable_autoshorturl = false;
	protected $allowed_protocols = array('http;','ftp:','https:','ftps:');
	protected $only_blog = false;

	public function __construct($core,$enable_autoshorturl=false,$allowed_protocols=array(),$only_blog=false)
	{
		$this->core = $core;
		$this->table = $core->prefix.'miniurl';
		$this->blog = $core->con->escape($core->blog->id);
		$this->con = $core->con;

		$this->enable_autoshorturl = (boolean) $enable_autoshorturl;
		if (!empty($allowed_protocols) && is_array($allowed_protocols)) {
			$this->allowed_protocols = $allowed_protocols;
		}
		$this->only_blog = (boolean) $only_blog;
	}

	public function isAllowed($str)
	{
		$find = false;
		foreach ($this->allowed_protocols as $protocol) {
			if (strtolower(substr($str, 0, strlen($protocol))) == strtolower($protocol)) {
				$find = true;
				break;
			}
		}
		return $find;
	}

	public static function isBlog($str)
	{
		global $core;
		$url = $core->blog->url;
		$str = substr($str,0,strlen($url));

		return $str == $url;
	}

	public static function isMini($str)
	{
		global $core;
		$url = $core->blog->url.$core->url->getBase('miniUrl').'/';
		$str = substr($str,0,strlen($url));

		return $str == $url;
	}

	public static function isLonger($str)
	{
		global $core;
		$url = $core->blog->url.$core->url->getBase('miniUrl');
		return strlen($str) > (strlen($url) + 2);
	}

	public function id($type,$str)
	{
		$rs = $this->select('miniurl_id',$type,null,$str);

		return $rs->isEmpty() ? -1 : $rs->miniurl_id;
	}

	public function str($type,$id)
	{
		$rs = $this->select('miniurl_str',$type,$id);

		return $rs->isEmpty() ? -1 : $rs->miniurl_str;
	}

	public function auto($str,$types=array())
	{
		if (!self::isLonger($str))
			return -1;

		if (self::isMini($str))
			return -1;

		if ($this->only_blog && !self::isBlog($str))
			return -1;

		if (!$this->isAllowed($str))
			return -1;

		if (empty($types) || !is_array($types))
			$types = array('miniurl','customurl');

		foreach($types as $type) {
			$rs = $this->select('miniurl_id',$type,null,$str);

			if (!$rs->isEmpty())
				return $rs->miniurl_id;
		}

		if ($this->enable_autoshorturl)
			return $this->create('miniurl',$str);
		else
			return -1;
	}

	public function create($type,$str,$custom=null)
	{
		if ($custom) {

			return (-1 == $this->str($type,$custom)) ?
				$this->insert($type,$str,$custom) : 
				-1;

		} else {

			return (-1 == $this->id($type,$str)) ? 
				$this->insert($type,$str,$this->next($type,$this->last($type))) :
				$this->id($type,$str);
		}
	}

	public function update($old_type,$old_id,$new_type,$new_id,$new_str)
	{
		$cur = $this->con->openCursor($this->table);
		$this->con->writeLock($this->table);

		$cur->miniurl_type = (string) $new_type;
		$cur->miniurl_id = (string) $new_id;
		$cur->miniurl_str = (string) $new_str;
		$cur->miniurl_dt = date('Y-m-d H:i:s');
		$cur->miniurl_counter = 0;

		$cur->update(
			"WHERE blog_id='".$this->blog."' ".
			"AND miniurl_type='".$this->con->escape($old_type)."' ".
			"AND miniurl_id='".$this->con->escape($old_id)."'"
		);
		$this->con->unlock();

		return $new_id;
	}

	public function delete($type,$id)
	{
		$this->con->execute(
			'DELETE FROM '.$this->table.' '.
			"WHERE blog_id='".$this->blog."' ".
			"AND miniurl_type='".$this->con->escape($type)."' ".
			"AND miniurl_id='".$this->con->escape($id)."'");

		return true;
	}

	private function insert($type,$str,$id)
	{
		$cur = $this->con->openCursor($this->table);
		$this->con->writeLock($this->table);

		$cur->blog_id = $this->blog;
		$cur->miniurl_type = (string) $type;
		$cur->miniurl_id = (string) $id;
		$cur->miniurl_str = (string) $str;
		$cur->miniurl_dt = date('Y-m-d H:i:s');

		$cur->insert();
		$this->con->unlock();

		return $id;
	}

	protected function last($type)
	{
		$rs = $this->select('miniurl_id',$type,null,null,true);

		return $rs->isEmpty() ? -1 : $rs->miniurl_id;
	}

	protected function next($type,$last_id)
	{
		if ($last_id == -1)
			$next_id = 0;

		else
		{
			for($x = 1; $x <= strlen($last_id); $x++) {

				$pos = strlen($last_id) - $x;

				if ($last_id[$pos] != 'z') {
					$next_id = $this->increment($last_id,$pos);
					break;
				}
			}

			if (!isset($next_id))
				$next_id = $this->append($last_id);
		}

		$rs = $this->select('miniurl_id',$type,$next_id);

		return $rs->isEmpty() ? $next_id : $this->next($type,$next_id);
	}

	protected function append($id)
	{
		for ($x = 0; $x < strlen($id); $x++) {
			$id[$x] = 0;
		}
		$id .= 0;

		return $id;
	}

	protected function increment($id,$pos)
	{
		$char = $id[$pos];

		if (is_numeric($char))
			$new_char = $char < 9 ? $char + 1 : 'a';

		else
			$new_char = chr(ord($char) + 1);

		$id[$pos] = $new_char;
		
		if ($pos != (strlen($id) - 1)) {
			for ($x = ($pos + 1); $x < strlen($id); $x++) {
				$id[$x] = 0;
			}
		}

		return $id;
	}

	public function counter($type,$id,$do='get')
	{
		$rs = $this->select('miniurl_counter',$type,$id);

		$counter = $rs->isEmpty() ? 0 : $rs->miniurl_counter;

		if ('get' == $do)
			return $counter;

		elseif ('up' == $do)
			$counter += 1;

		elseif ('reset' == $do)
			$counter = 0;

		else
			return 0;

		$cur = $this->con->openCursor($this->table);
		$this->con->writeLock($this->table);

		$cur->miniurl_counter = (integer) $counter;
		$cur->update(
			"WHERE blog_id='".$this->blog."' ".
			"AND miniurl_type='".$this->con->escape($type)."' ".
			"AND miniurl_id='".$this->con->escape($id)."'"
		);
		$this->con->unlock();

		return $counter;
	}

	private function select($field,$type,$id,$str=null,$by_dt=false)
	{
		$req = 
		'SELECT '.$field.' FROM '.$this->table.' '.
		"WHERE blog_id='".$this->blog."' ".
		"AND miniurl_type='".$this->con->escape($type)."' ";

		if (!empty($id))
			$req .= "AND miniurl_id='".$this->con->escape($id)."' ";

		if (!empty($str))
			$req .= "AND miniurl_str='".$this->con->escape($str)."' ";

		if ($by_dt)
			$req .= 'ORDER BY miniurl_dt DESC ';

		return $this->con->select($req.$this->con->limit(1));
	}

	public function getMiniUrls($p=array(),$count_only=false)
	{
		if ($count_only)
			$strReq = 'SELECT count(S.miniurl_id) ';

		else {
			$content_req = '';
			
			if (!empty($p['columns']) && is_array($p['columns']))
				$content_req .= implode(', ',$p['columns']).', ';

			$strReq = 'SELECT S.miniurl_type, S.miniurl_id, S.miniurl_str, '.
				'S.miniurl_counter, '.$content_req.'S.miniurl_dt ';
		}

		$strReq .= 'FROM '.$this->table.' S ';

		if (!empty($p['from']))
			$strReq .= $p['from'].' ';

		$strReq .= "WHERE S.blog_id = '".$this->blog."' ";

		if (isset($p['miniurl_type'])) {

			if (is_array($p['miniurl_type']) && !empty($p['miniurl_type']))
				$strReq .= 'AND miniurl_type '.$this->con->in($p['miniurl_type']);

			elseif ($p['miniurl_type'] != '')
				$strReq .= "AND miniurl_type = '".$this->con->escape($p['miniurl_type'])."' ";
		}
		else
			$strReq .= 'AND miniurl_type '.$this->con->in(array('miniurl','customurl'));

		if (isset($p['miniurl_id'])) {

			if (is_array($p['miniurl_id']) && !empty($p['miniurl_id']))
				$strReq .= 'AND miniurl_id '.$this->con->in($p['miniurl_id']);

			elseif ($p['miniurl_id'] != '')
				$strReq .= "AND miniurl_id = '".$this->con->escape($p['miniurl_id'])."' ";
		}

		if (isset($p['miniurl_str'])) {

			if (is_array($p['miniurl_str']) && !empty($p['miniurl_str']))
				$strReq .= 'AND miniurl_str '.$this->con->in($p['miniurl_str']);

			elseif ($p['miniurl_str'] != '')
				$strReq .= "AND miniurl_str = '".$this->con->escape($p['miniurl_str'])."' ";
		}

		if (!empty($p['miniurl_year'])) {
			$strReq .= 'AND '.$this->con->dateFormat('miniurl_dt','%Y').' = '.
			"'".sprintf('%04d',$p['miniurl_year'])."' ";
		}

		if (!empty($p['miniurl_month'])) {
			$strReq .= 'AND '.$this->con->dateFormat('miniurl_dt','%m').' = '.
			"'".sprintf('%02d',$p['miniurl_month'])."' ";
		}

		if (!empty($p['miniurl_day'])) {
			$strReq .= 'AND '.$this->con->dateFormat('miniurl_dt','%d').' = '.
			"'".sprintf('%02d',$p['miniurl_day'])."' ";
		}

		if (!empty($p['sql'])) 
			$strReq .= $p['sql'].' ';

		if (!$count_only) {
			$strReq .= empty($p['order']) ?
				'ORDER BY miniurl_dt DESC ' :
				'ORDER BY '.$this->con->escape($p['order']).' ';
		}

		if (!$count_only && !empty($p['limit'])) 
			$strReq .= $this->con->limit($p['limit']);

		return $this->con->select($strReq);
	}
}
?>