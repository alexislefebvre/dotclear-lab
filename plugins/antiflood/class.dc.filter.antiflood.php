<?php
# ***** BEGIN LICENSE BLOCK *****
# This is Antiflood,a spam filter for DotClear. 
# Copyright (c) 2007 dcTeam and contributors. All rights reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

class dcFilterAntiFlood extends dcSpamFilter
{
	public $name = 'Anti Flood';
	public $has_gui = true;
	public $delay;
	public $send_error;
	private $con;
	private $table;
	
	public function __construct(&$core)
	{
		parent::__construct($core);
		$this->con =& $core->con;
		$this->table = $core->prefix.'spamrule';
		$blog =& $this->core->blog;
		$this->delay = $blog->settings->flood_delay;
		$this->send_error = $blog->settings->send_error;

		if ($this->delay == null ) {
			$blog->settings->setNameSpace('antiflood');
			$blog->settings->put('flood_delay',60,'integer','Delay in seconds beetween two comments from the same IP');
			$this->delay = 60;
		}
		if ($this->send_error == null ) {
			$blog->settings->setNameSpace('antiflood');
			$blog->settings->put('send_error',false,'boolean','Whether the filter should reply with a 503 error code');
			$this->send_error = false;
		}

	}
	
	protected function setInfo()
	{
		$this->description = __('Anti flood');
	}

	public function isSpam($type,$author,$email,$site,$ip,$content,$post_id,&$status)
	{
		if ($this->checkIp($ip)) {
			if ($this->send_error) {
				http::head(503,'Service Unavailable');
				exit;
			}
			return(NULL);
		}
		return(FALSE);
	}

	public function getStatusMessage($status,$comment_id)
	{
		return sprintf(__('Filtered by %s.'),$this->guiLink());
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

	public function gui($url)
	{
		$blog =& $this->core->blog;
		
		$flood_delay = $blog->settings->flood_delay;
		$send_error = $blog->settings->send_error;
		
		if (isset($_POST['flood_delay']) && isset($_POST['send_error']))
		{
			try
			{
				$flood_delay = $_POST['flood_delay'];
				$send_error = $_POST['send_error'];
				
				$blog->settings->setNameSpace('antiflood');
				$blog->settings->put('flood_delay',$flood_delay,'string');
				$blog->settings->put('send_error',$flood_delay,'boolean');
				
				http::redirect($url.'&up=1');
			}
			catch (Exception $e)
			{
				$this->core->error->add($e->getMessage());
			}
		}
		
		$res =
		'<form action="'.html::escapeURL($url).'" method="post">'.
		'<p><label class="classic">'.__('Delay:').' '.
		form::field('flood_delay',12,128,$flood_delay).'</label>';
		
		$res .= '</p>';
		
		$res .=
		'<p>'.__('Sets the delay in seconds beetween two comments from the same IP').'</p>'.
		'<p><label class="classic">'.__('Send error code:').' '.
		form::checkbox('send_error',$send_error,$send_error).'</label>';
		
		$res .= '</p>';
		
		$res .=
		'<p>'.__('Sets whether the filter should reply with a 503 error code.').'</p>'.
		'<p><input type="submit" value="'.__('save').'" /></p>'.
		'</form>';
		
		return $res;
	}

}
?>