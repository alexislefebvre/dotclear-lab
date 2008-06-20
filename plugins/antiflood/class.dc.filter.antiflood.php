<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Antiflood,a spam filter for Dotclear 2.
#
# Copyright (c) 2003-2008 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

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
				echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">'.
					'<HTML><HEAD>'.
					'<TITLE>503 '.__('Service Temporarily Unavailable').'</TITLE>'.
					'</HEAD><BODY>'.
					'<H1>'.__('Service Temporarily Unavailable').'</H1>'.
					__('The server is temporarily unable to service your request due to maintenance downtime or capacity problems. Please try again later.').
					'</BODY></HTML>';
				exit;
			}
			return(TRUE);
		}
		return(NULL);
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
		if (count($ids)>0) {$this->removeRule($ids);}
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
		
		if (isset($_POST['flood_delay']))
		{
			try
			{
				$flood_delay = $_POST['flood_delay'];
				$send_error = isset($_POST['send_error']);
				
				$blog->settings->setNameSpace('antiflood');
				$blog->settings->put('flood_delay',$flood_delay,'string');
				$blog->settings->put('send_error',$send_error,'boolean');
				
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
		form::checkbox('send_error',1,$send_error).'</label>';
		
		$res .= '</p>';
		
		$res .=
		'<p>'.__('Sets whether the filter should reply with a 503 error code.').'</p>'.
		'<p><input type="submit" value="'.__('save').'" />'.
		$this->core->formNonce().'</p>'.
		'</form>';
		
		return $res;
	}

}
?>
