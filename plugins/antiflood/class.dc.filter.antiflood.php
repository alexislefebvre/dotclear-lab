<?php
class dcFilterAntiFlood extends dcSpamFilter
{
	public $name = 'Anti Flood Filter';
	public $has_gui = false;
	public $delay = 60;
	private $con;
	private $table;
	
	public function __construct(&$core)
	{
		parent::__construct($core);
		$this->con =& $core->con;
		$this->table = $core->prefix.'spamrule';
	}
	
	protected function setInfo()
	{
		$this->description = __('Anti flood spam filter');
	}

	public function isSpam($type,$author,$email,$site,$ip,$content,$post_id,&$status)
	{
		return $this->checkIp($ip);
	}

	public function getStatusMessage($status,$comment_id)
	{
		return sprintf(__('Filtered by %s.'),$this->guiLink());
	}

	public function trainFilter($status,$filter,$type,$author,$email,$site,$ip,$content,$rs)
	{
		// Choses à faire pour entraîner le filtre
	}

	private function checkIP($cip)
	{
		$core =& $this->core;
		
		$strReq =
		'SELECT DISTINCT(rule_content) '.
		'FROM '.$this->table.' '.
		"WHERE rule_type = 'flood' ".
		"AND (blog_id = '".$this->core->blog->id."' OR blog_id IS NULL) ".
		'ORDER BY rule_content ASC ';
		
		$rs = $this->con->select($strReq);
		while ($rs->fetch())
		{
			list($ip,$time) = explode(':',$rs->rule_content);
			if (($cip == $ip) && (time()-$time <= $this->delay)) {
				return true;
			}
		}

		$this->cleanOldRecords();

		$cur = $this->con->openCursor($this->table);
		
		$id = $this->con->select('SELECT MAX(rule_id) FROM '.$this->table)->f(0) + 1;
		
		$cur->rule_id = $id;
		$cur->rule_type = 'flood';
		$cur->rule_content = (string) implode(':',array($cip,time()));
		$cur->blog_id = $this->core->blog->id;
			
		$cur->insert();

		return false;
	}

	private function cleanOldRecords()
	{
		$core =& $this->core;
		$ids = array();
		
		$strReq =
		'SELECT rule_id, rule_content '.
		'FROM '.$this->table.' '.
		"WHERE rule_type = 'flood' ".
		"AND (blog_id = '".$this->core->blog->id."' OR blog_id IS NULL) ".
		'ORDER BY rule_content ASC ';
		
		$rs = $this->con->select($strReq);
		while ($rs->fetch())
		{
			list($ip,$time) = explode(':',$rs->rule_content);
			if (time()-$time > $this->delay) {
				array_push ($ids, $rs->rule_id);
			}
		}
		$this->removeRule($ids);		
	}

	private function removeRule($ids)
	{
		$strReq = 'DELETE FROM '.$this->table.' ';
		
		if (is_array($ids)) {
			foreach ($ids as &$v) {
				$v = (integer) $v;
			}
			$strReq .= 'WHERE rule_id IN ('.implode(',',$ids).') ';
		} else {
			$ids = (integer) $ids;
			$strReq .= 'WHERE rule_id = '.$ids.' ';
		}
		
		if (!$this->core->auth->isSuperAdmin()) {
			$strReq .= "AND blog_id = '".$this->con->escape($this->core->blog->id)."' ";
		}
		
		$this->con->execute($strReq);
	}
}
?>
